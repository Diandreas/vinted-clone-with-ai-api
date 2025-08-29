<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Exception;

class NotchPayController extends Controller
{
    /**
     * Initialize a new payment with NotchPay for product listing fee
     */
    public function initializePayment(Request $request)
    {
        Log::info('Starting NotchPay payment initialization for product', [
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        try {
            // 1. Validate request
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'amount' => 'required|numeric|min:100',
                'email' => 'required|email',
            ]);

            $user = auth()->user();
            if (!$user) {
                throw new Exception('User not authenticated');
            }

            // 2. Verify product ownership and status
            $product = \App\Models\Product::findOrFail($validated['product_id']);
            if ($product->user_id !== $user->id) {
                throw new Exception('Unauthorized access to product');
            }

            if ($product->status !== \App\Models\Product::STATUS_PENDING_PAYMENT) {
                throw new Exception('Product is not pending payment');
            }

            // 3. Initialize payment with NotchPay API
            $fields = [
                'email' => $validated['email'],
                'amount' => (string)$validated['amount'],
                'currency' => 'XAF',
                'description' => 'Frais de publication - ' . $product->name,
                'reference' => 'prod_' . $product->id . '_' . uniqid(),
                'callback' => route('payment.callback'),
                'sandbox' => config('services.notchpay.sandbox', false) // Disable sandbox in production
            ];

            Log::info('Initiating NotchPay API request', [
                'fields' => $fields,
                'endpoint' => 'payments/initialize'
            ]);

            $response = $this->makeNotchPayRequest('payments/initialize', $fields);

            if (!isset($response['authorization_url'])) {
                throw new Exception('Invalid response from NotchPay');
            }

            Log::info('Creating payment record', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'amount' => $validated['amount'],
                'response' => $response
            ]);

            // 4. Create pending payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'currency' => 'XAF',
                'transaction_id' => $response['transaction']['reference'],
                'status' => 'pending',
                'payment_method' => 'notchpay',
                'metadata' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'authorization_url' => $response['authorization_url'],
                    'notchpay_reference' => $response['transaction']['reference'],
                    'email' => $validated['email']
                ]
            ]);

            Log::info('NotchPay payment initialized for product', [
                'payment_id' => $payment->id,
                'product_id' => $product->id,
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

    /**
     * Handle NotchPay callback after payment
     */
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
            // Verify the payment status
            if ($request->status !== 'complete') {
                Log::warning('Payment not complete', ['status' => $request->status]);
                throw new Exception('Payment was not completed');
            }

            // Vérifier que nous avons au moins une référence valide
            if (!$request->notchpay_trxref && !$request->reference && !$request->trxref) {
                Log::warning('No valid reference found in callback');
                throw new Exception('Aucune référence de paiement valide trouvée');
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
                throw new Exception('Payment not found or already processed');
            }

            // Vérifier que le paiement a bien les métadonnées nécessaires
            if (!isset($payment->metadata['product_id'])) {
                throw new Exception('Métadonnées de paiement invalides');
            }

            $metadata = $payment->metadata;
            $productId = $metadata['product_id'];

            // Get the product
            $product = \App\Models\Product::findOrFail($productId);
            
            // Vérifier que le produit est toujours en attente de paiement
            if ($product->status !== 'pending_payment') {
                throw new Exception('Le produit n\'est plus en attente de paiement');
            }
            
            // Update payment status
            $payment->markAsCompleted();

            // Activate the specific product
            $this->activateProductAfterPayment($product);



            Log::info('Payment completed successfully', [
                'payment_id' => $payment->id,
                'product_id' => $product->id,
                'user_id' => $product->user->id,
                'product_name' => $product->name
            ]);

            DB::commit();

            // Store success message in session and redirect to public result page
            session()->flash('payment_success', 'Paiement réussi ! Le produit "' . $product->name . '" a été activé avec succès.');
            return redirect()->route('payment.result');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // Store error message in session and redirect to public result page
            session()->flash('payment_error', 'Erreur lors du traitement du paiement : ' . $e->getMessage());
            return redirect()->route('payment.result');
        }
    }

    /**
     * Handle NotchPay webhook for real-time updates
     */
    public function handleWebhook(Request $request)
    {
        Log::info('Received NotchPay webhook', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // 1. Verify webhook signature
            $signature = $request->header('x-notch-signature');
            $hash = hash('sha256', config('services.notchpay.webhook_secret'));

            if (!hash_equals($hash, $signature)) {
                throw new Exception('Invalid webhook signature');
            }

            // 2. Process webhook data
            $payload = $request->all();

            if ($payload['event'] !== 'payment.complete') {
                return response()->json(['status' => 'ignored']);
            }

            DB::beginTransaction();

            try {
                // 3. Update payment record
                $payment = Payment::where('transaction_id', $payload['data']['reference'])
                    ->where('status', 'pending')
                    ->firstOrFail();

                $metadata = $payment->metadata;
                $productId = $metadata['product_id'];

                // 4. Get the product
                $product = \App\Models\Product::findOrFail($productId);

                // 5. Update payment status
                $payment->markAsCompleted();

                // 6. Activate the specific product
                $this->activateProductAfterPayment($product);

                

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
     * Activate a specific product after successful payment
     */
    private function activateProductAfterPayment(Product $product): void
    {
        try {
            // Update product status to active
            $product->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

            // Create or update ProductFeeCharge record
            $feeCharge = \App\Models\ProductFeeCharge::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'fee_id' => \App\Models\PlatformFee::where('code', 'listing_fee')->first()->id ?? 1
                ],
                [
                    'amount' => $product->listing_fee ?? 0,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'notchpay'
                ]
            );

            Log::info('Product activated after payment', [
                'product_id' => $product->id,
                'user_id' => $product->user_id,
                'product_name' => $product->name,
                'fee_charge_id' => $feeCharge->id
            ]);

        } catch (Exception $e) {
            Log::error('Error activating product after payment', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }



    /**
     * Make API request to NotchPay
     */
    private function makeNotchPayRequest(string $endpoint, array $data): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => config('services.notchpay.base_url') . '/' . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . config('services.notchpay.public_key'),
                'Content-Type: application/json',
                'Accept: application/json',
                'Cache-Control: no-cache',
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
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

    /**
     * Show payment result page (public)
     */
    public function showPaymentResult()
    {
        $success = session('payment_success');
        $error = session('payment_error');
        
        return view('payment.result', compact('success', 'error'));
    }
}