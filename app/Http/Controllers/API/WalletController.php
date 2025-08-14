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
     * Top up wallet using Fapshi.
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount_xaf' => 'required|integer|min:100|max:1000000', // 100 FCFA to 1M FCFA
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $amount = $request->amount_xaf;

        try {
            // Initialize Fapshi payment
            $fapshi = app(\App\Services\Payment\FapshiService::class);
            
            $response = $fapshi->initiatePay([
                'amount' => $amount,
                'currency' => 'XAF',
                'email' => $user->email,
                'phone' => $request->phone ?? $user->phone,
                'description' => $request->message ?? "Recharge wallet {$user->name}",
                'callback_url' => route('api.wallet.fapshi-callback'),
                'return_url' => route('api.wallet.fapshi-return'),
            ]);

            if ($response['success']) {
                // Create pending wallet transaction
                $transaction = WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'topup',
                    'amount_xaf' => $amount,
                    'status' => 'pending',
                    'provider' => 'fapshi',
                    'trans_id' => $response['data']['transId'] ?? null,
                    'metadata' => $response,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'fapshi_data' => $response['data'],
                        'redirect_url' => $response['data']['redirectUrl'] ?? null,
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
     * Withdraw funds from wallet.
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount_xaf' => 'required|integer|min:100',
            'phone' => 'required|string|max:20',
            'provider' => 'required|in:om,momo',
        ]);

        $user = Auth::user();
        $amount = $request->amount_xaf;

        // Check if user has sufficient balance
        if (($user->wallet_balance_xaf ?? 0) < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant'
            ], 400);
        }

        try {
            // Create withdrawal transaction
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount_xaf' => $amount,
                'status' => 'pending',
                'provider' => $request->provider,
                'metadata' => [
                    'phone' => $request->phone,
                    'provider' => $request->provider,
                ],
            ]);

            // Deduct from wallet balance
            $user->decrement('wallet_balance_xaf', $amount);

            // Here you would integrate with OM/MoMo API for actual withdrawal
            // For now, we'll simulate success
            $transaction->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Retrait initié avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Wallet withdrawal error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait'
            ], 500);
        }
    }

    /**
     * Fapshi webhook callback.
     */
    public function fapshiCallback(Request $request)
    {
        $payload = json_decode($request->getContent() ?: '{}', true);
        
        if (!isset($payload['transId'])) {
            return response()->json(['success' => false], 400);
        }

        try {
            $fapshi = app(\App\Services\Payment\FapshiService::class);
            $event = $fapshi->paymentStatus($payload['transId']);

            $transaction = WalletTransaction::where('trans_id', $payload['transId'])
                ->where('type', 'topup')
                ->first();

            if (!$transaction) {
                return response()->json(['success' => false], 404);
            }

            $user = $transaction->user;

            switch ($event['status'] ?? null) {
                case 'SUCCESSFUL':
                    // Add funds to wallet
                    $user->increment('wallet_balance_xaf', $transaction->amount_xaf);
                    $transaction->update(['status' => 'completed']);
                    
                    // Send notification to user
                    $user->notify(new \App\Notifications\WalletRecharged($transaction));
                    break;

                case 'FAILED':
                case 'EXPIRED':
                    $transaction->update(['status' => 'failed']);
                    break;
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Fapshi callback error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Fapshi return URL (user redirected back).
     */
    public function fapshiReturn(Request $request)
    {
        // This would typically redirect to the mobile app or show a success page
        return response()->json([
            'success' => true,
            'message' => 'Retour de Fapshi'
        ]);
    }
}
