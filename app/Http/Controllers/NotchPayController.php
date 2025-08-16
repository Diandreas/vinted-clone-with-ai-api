<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralEarning;
use App\Models\User;
use Exception;

class NotchPayController extends Controller
{
    public function handleCallback(Request $request)
    {
        Log::info('NotchPay callback received', [
            'parameters' => $request->all(),
            'reference' => $request->reference,
            'trxref' => $request->trxref,
            'notchpay_trxref' => $request->notchpay_trxref,
            'status' => $request->status
        ]);

        try {
            // Handle different payment statuses
            if ($request->status === 'canceled') {
                Log::info('Payment was canceled by user', [
                    'reference' => $request->reference,
                    'status' => $request->status
                ]);
                return redirect('/payment')
                    ->with('warning', 'Paiement annulé. Vous pouvez réessayer à tout moment.');
            }

            if ($request->status === 'failed') {
                Log::warning('Payment failed', [
                    'reference' => $request->reference,
                    'status' => $request->status
                ]);
                return redirect('/payment')
                    ->with('error', 'Le paiement a échoué. Veuillez réessayer.');
            }

            if ($request->status !== 'complete') {
                Log::warning('Unknown payment status', ['status' => $request->status]);
                return redirect('/payment')
                    ->with('error', 'Statut de paiement inconnu: ' . $request->status);
            }

            DB::beginTransaction();

            // Try to find the payment using both reference and trxref
            $payment = Payment::where(function($query) use ($request) {
                $query->where('transaction_id', $request->notchpay_trxref)
                    ->orWhere('transaction_id', $request->reference)
                    ->orWhere('transaction_id', $request->trxref);
            })
                ->where('status', 'pending')
                ->first();

            Log::info('Found payment record', [
                'payment' => $payment ? $payment->toArray() : 'Not found',
                'searched_refs' => [
                    'notchpay_trxref' => $request->notchpay_trxref,
                    'reference' => $request->reference,
                    'trxref' => $request->trxref
                ]
            ]);

            if (!$payment) {
                Log::warning('Payment not found', [
                    'searched_refs' => [
                        'notchpay_trxref' => $request->notchpay_trxref,
                        'reference' => $request->reference,
                        'trxref' => $request->trxref
                    ]
                ]);
                return redirect('/payment')
                    ->with('error', 'Paiement introuvable ou déjà traité.');
            }

            $metadata = json_decode($payment->metadata, true);
            $tokens = $metadata['tokens'];

            // Update user balance
            $user = $payment->user;
            $oldBalance = $user->wallet_balance;
            $user->wallet_balance += $tokens;
            $user->save();

            // Update payment status
            $payment->status = 'completed';
            $payment->save();

            // Process referral commission if the user was referred
            $this->processReferralCommission($user, $payment->amount);

            Log::info('Payment completed successfully', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'old_balance' => $oldBalance,
                'new_balance' => $user->wallet_balance,
                'tokens_added' => $tokens
            ]);

            DB::commit();

            // Redirect to success page with success message
            return redirect('/payment')
                ->with('success', 'Paiement réussi ! ' . $tokens . ' jetons ont été ajoutés à votre compte.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // Redirect to payment page with error message
            return redirect()->route('payment.index')
                ->with('error', 'Erreur lors du traitement du paiement : ' . $e->getMessage());
        }
    }

    public function initializePayment(Request $request)
    {
        Log::info('Starting NotchPay payment initialization', [
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        try {
            // 1. Validate request
            $validated = $request->validate([
                'tokens' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:100',
                'email' => 'required|email'
            ]);

            $user = auth()->user();
            if (!$user) {
                throw new Exception('User not authenticated');
            }

            // 2. Use amount from request
            $amount = $validated['amount'];

            // 3. Initialize payment with NotchPay API
            $reference = 'tok_' . uniqid() . '_' . time();
            $fields = [
                'email' => $validated['email'],
                'amount' => (string)$amount,
                'currency' => 'XAF',
                'description' => $validated['tokens'] . ' tokens purchase',
                'reference' => $reference,
                'callback' => config('services.notchpay.callback_url'),
                'sandbox' => config('services.notchpay.sandbox', true)
            ];

            Log::info('Initiating NotchPay API request', [
                'fields' => $fields,
                'endpoint' => 'payments/initialize'
            ]);

            $response = $this->makeNotchPayRequest('payments/initialize', $fields);

            if (!isset($response['authorization_url'])) {
                throw new Exception('Invalid response from NotchPay: ' . json_encode($response));
            }

            Log::info('Creating payment record', [
                'user_id' => $user->id,
                'amount' => $amount,
                'response' => $response
            ]);

            // 4. Create pending payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'transaction_id' => $reference,
                'status' => 'pending',
                'payment_method' => 'notchpay',
                'metadata' => json_encode([
                    'tokens' => $validated['tokens'],
                    'authorization_url' => $response['authorization_url'],
                    'notchpay_reference' => $response['transaction']['reference'] ?? $reference
                ])
            ]);

            Log::info('NotchPay payment initialized', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id
            ]);

            return response()->json([
                'success' => true,
                'authorization_url' => $response['authorization_url']
            ]);

        } catch (Exception $e) {
            Log::error('NotchPay payment initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Received NotchPay webhook', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Process webhook data
            $payload = $request->all();

            if (!isset($payload['event']) || $payload['event'] !== 'payment.complete') {
                Log::info('Webhook ignored', ['event' => $payload['event'] ?? 'unknown']);
                return response()->json(['status' => 'ignored']);
            }

            DB::beginTransaction();

            try {
                // Find payment record
                $reference = $payload['data']['reference'] ?? null;
                if (!$reference) {
                    throw new Exception('No reference found in webhook data');
                }

                $payment = Payment::where('transaction_id', $reference)
                    ->where('status', 'pending')
                    ->first();

                if (!$payment) {
                    throw new Exception('Payment not found or already processed');
                }

                $metadata = json_decode($payment->metadata, true);
                $tokens = $metadata['tokens'];

                // Update user balance
                $user = $payment->user;
                $oldBalance = $user->wallet_balance;
                $user->wallet_balance += $tokens;
                $user->save();

                // Update payment status
                $payment->status = 'completed';
                $payment->save();

                // Process referral commission if the user was referred
                $this->processReferralCommission($user, $payment->amount);

                DB::commit();

                Log::info('NotchPay payment completed successfully via webhook', [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'old_balance' => $oldBalance,
                    'new_balance' => $user->wallet_balance
                ]);

                return response()->json(['status' => 'success']);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('NotchPay webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process referral commission when a referred user makes a purchase
     */
    private function processReferralCommission($user, $amount)
    {
        try {
            // Check if user was referred by someone
            $referral = Referral::where('referred_id', $user->id)->first();

            if (!$referral) {
                return; // No referral found, exit
            }

            // Get the referrer
            $referrer = User::find($referral->referrer_id);

            if (!$referrer) {
                Log::error('Referrer not found during commission processing', [
                    'referrer_id' => $referral->referrer_id,
                    'referred_id' => $user->id
                ]);
                return;
            }

            // Get referrer's level to determine commission percentage
            $level = $referrer->referralLevel();

            // Get commission rate based on level
            $commissionRates = [
                'ARGENT' => 0.10, // 10%
                'OR' => 0.15,     // 15%
                'DIAMANT' => 0.20 // 20%
            ];

            $commissionRate = $commissionRates[$level] ?? 0.10;

            // Calculate commission amount
            $commissionAmount = $amount * $commissionRate;

            // Record the earning
            $earning = ReferralEarning::create([
                'user_id' => $referrer->id,
                'referral_id' => $referral->id,
                'amount' => $commissionAmount,
                'status' => 'pending'
            ]);

            // Directly add to referrer's wallet balance (immediate payout)
            $referrer->wallet_balance += $commissionAmount;
            $referrer->save();

            // Update earning status to paid
            $earning->status = 'paid';
            $earning->save();

            Log::info('Referral commission processed', [
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'purchase_amount' => $amount,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount
            ]);

        } catch (Exception $e) {
            Log::error('Error processing referral commission', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'amount' => $amount
            ]);
        }
    }

    private function makeNotchPayRequest($endpoint, $data)
    {
        $ch = curl_init();

        // Fixed: Proper Authorization header format for NotchPay (no Bearer prefix)
        $headers = [
            'Authorization: ' . config('services.notchpay.public_key'),
            'Content-Type: application/json',
            'Accept: application/json',
            'Cache-Control: no-cache',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => config('services.notchpay.base_url') . '/' . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false, // For sandbox mode
            CURLOPT_VERBOSE => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        
        Log::info('NotchPay API Request', [
            'url' => config('services.notchpay.base_url') . '/' . $endpoint,
            'headers' => $headers,
            'data' => $data,
            'response' => $response,
            'http_code' => $httpCode
        ]);
        
        curl_close($ch);

        if ($err) {
            throw new Exception('Failed to connect to NotchPay: ' . $err);
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            Log::error('NotchPay API error', [
                'status_code' => $httpCode,
                'response' => $decodedResponse,
                'request_data' => $data
            ]);
            throw new Exception('NotchPay API error: ' . ($decodedResponse['message'] ?? 'Unknown error'));
        }

        return $decodedResponse;
    }

    private function calculateAmount($tokens)
    {
        // This should match your frontend TOKEN_PACKS logic
        $tokenPacks = [
            10 => 600,
            25 => 1200,
            60 => 3000,
            130 => 6000,
            400 => 18000
        ];

        return $tokenPacks[$tokens] ?? ($tokens * 100); // Default to 100 XAF per token
    }
}