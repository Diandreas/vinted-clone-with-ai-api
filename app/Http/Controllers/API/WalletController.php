<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    /**
     * Get user's wallet balance and information.
     */
    public function balance()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'balance_xaf' => $user->wallet_balance_xaf ?? 0,
                'balance_formatted' => number_format($user->wallet_balance_xaf ?? 0) . ' FCFA',
                'currency' => 'XAF',
                'last_updated' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Get wallet transaction history.
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->with(['order', 'product'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Top up wallet using NotchPay.
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount_xaf' => 'required|integer|min:100|max:1000000', // 100 FCFA to 1M FCFA
            'message' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $amount = $request->amount_xaf;

        try {
            // Initialize NotchPay payment
            $notchpay = app(\App\Services\Payment\NotchPayService::class);
            
            $reference = 'wallet_' . uniqid();
            $response = $notchpay->initializePayment([
                'amount' => (string)$amount,
                'currency' => 'XAF',
                'email' => $user->email,
                'description' => $request->message ?? "Recharge wallet {$user->name}",
                'reference' => $reference,
                'callback' => url('/api/v1/webhooks/notchpay'),
            ]);

            // Log the response for debugging
            Log::info('NotchPay initialize payment response:', $response);

            // Check if the response is successful
            if (($response['statusCode'] ?? 500) === 200 || ($response['statusCode'] ?? 500) === 201) {
                // Create pending wallet transaction
                $transaction = WalletTransaction::create([
                    'user_id' => $user->id,
                    'purpose' => 'topup',
                    'amount_xaf' => $amount,
                    'status' => 'pending',
                    'provider' => 'notchpay',
                    'trans_id' => $reference,
                    'metadata' => $response,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'notchpay_data' => $response,
                        'authorization_url' => $response['authorization_url'] ?? null,
                    ],
                    'message' => 'Recharge initiée avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Wallet topup error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique lors de la recharge'
            ], 500);
        }
    }


    /**
     * NotchPay webhook callback.
     */
    public function notchpayCallback(Request $request)
    {
        $payload = $request->all();
        
        Log::info('NotchPay callback received:', $payload);
        
        if (!isset($payload['reference'])) {
            return response()->json(['success' => false], 400);
        }

        try {
            $notchpay = app(\App\Services\Payment\NotchPayService::class);
            $statusResponse = $notchpay->verifyPayment($payload['reference']);

            Log::info('NotchPay status check response:', $statusResponse);

            $transaction = WalletTransaction::where('trans_id', $payload['reference'])
                ->where('purpose', 'topup')
                ->first();

            if (!$transaction) {
                Log::warning('Transaction not found for reference: ' . $payload['reference']);
                return response()->json(['success' => false], 404);
            }

            $user = $transaction->user;

            // NotchPay status can be: complete, pending, failed
            switch ($payload['status'] ?? $statusResponse['status'] ?? null) {
                case 'complete':
                case 'completed':
                case 'successful':
                    if ($transaction->status !== 'completed') {
                        // Add funds to wallet
                        $user->increment('wallet_balance_xaf', $transaction->amount_xaf);
                        $transaction->update(['status' => 'completed']);
                        
                        Log::info('Wallet recharged successfully', [
                            'user_id' => $user->id,
                            'amount' => $transaction->amount_xaf,
                            'transaction_id' => $transaction->id
                        ]);
                    }
                    break;

                case 'failed':
                case 'cancelled':
                case 'expired':
                    $transaction->update(['status' => 'failed']);
                    break;

                case 'pending':
                    // Transaction still in progress, no action needed
                    break;
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('NotchPay callback error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * NotchPay return URL (user redirected back).
     */
    public function notchpayReturn(Request $request)
    {
        // This would typically redirect to the wallet page or show a success page
        return redirect()->route('wallet')
            ->with('success', 'Paiement traité. Vérifiez votre solde.');
    }
}
