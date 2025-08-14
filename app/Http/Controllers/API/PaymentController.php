<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
// use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Stripe removed for Cameroon market
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct() {}

    public function getMethods()
    {
        $paymentMethods = Auth::user()->paymentMethods()
            ->active()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    public function addMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'nullable|string',
            'is_default' => 'boolean'
        ]);

        return response()->json(['success' => false, 'message' => 'Card payment disabled'], 410);
    }

    public function updateMethod($method, Request $request)
    {
        $paymentMethod = Auth::user()->paymentMethods()->findOrFail($method);
        
        $request->validate([
            'is_default' => 'boolean'
        ]);

        if ($request->is_default) {
            Auth::user()->paymentMethods()->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($request->only(['is_default']));

        return response()->json([
            'success' => true,
            'data' => $paymentMethod,
            'message' => 'Payment method updated'
        ]);
    }

    public function deleteMethod($method)
    {
        $paymentMethod = Auth::user()->paymentMethods()->findOrFail($method);
        
        $paymentMethod->delete();
        return response()->json(['success' => true, 'message' => 'Payment method deleted']);
    }

    public function setDefault($method)
    {
        $paymentMethod = Auth::user()->paymentMethods()->findOrFail($method);
        
        // Remove default from others
        Auth::user()->paymentMethods()->update(['is_default' => false]);
        
        // Set as default
        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default payment method updated'
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'provider' => 'nullable|in:fapshi',
            'phone' => 'nullable|string',
        ]);

        $order = Order::findOrFail($request->order_id);
        
        // Verify user is the buyer
        if ($order->buyer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Cameroon-specific: use Fapshi (direct debit on buyer balance/phone)
        $provider = $request->provider ?? 'fapshi';
        if ($provider !== 'fapshi') {
            return response()->json(['success' => false, 'message' => 'Unsupported provider'], 400);
        }

        $buyer = Auth::user();
        $rate = (float) config('services.fapshi.xaf_per_eur', 650);
        $amountXaf = (int) round(((float) $order->total_amount) * $rate);

        $payload = [
            'amount' => $amountXaf,
            'email' => $buyer->email,
            'externalId' => (string) $order->order_number,
            'userId' => (string) $buyer->id,
            'message' => 'Order payment #' . $order->order_number,
        ];
        if ($request->filled('phone')) {
            $payload['phone'] = $request->string('phone')->toString();
        }

        $fapshi = app(\App\Services\Payment\FapshiService::class);
        $response = $request->filled('phone')
            ? $fapshi->directPay($payload)
            : $fapshi->initiatePay($payload);

        // Record wallet transaction for transparency
        $transId = $response['transId'] ?? null;
        if (class_exists(\App\Models\WalletTransaction::class)) {
            \App\Models\WalletTransaction::create([
                'user_id' => $buyer->id,
                'type' => 'debit',
                'purpose' => 'order_payment',
                'amount_xaf' => $amountXaf,
                'status' => 'pending',
                'provider' => 'fapshi',
                'trans_id' => $transId,
                'order_id' => $order->id,
                'metadata' => $response,
            ]);
        }

        // Set order as pending payment and return fapshi transaction info
        $order->update([
            'payment_status' => \App\Models\Order::PAYMENT_STATUS_PENDING,
            'status' => \App\Models\Order::STATUS_PENDING,
            'payment_method' => 'fapshi',
            'payment_intent_id' => $transId,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => 'fapshi',
                'status' => $response['status'] ?? 'PENDING',
                'transaction' => $response,
            ],
            'message' => 'Payment initiated with Fapshi'
        ]);

        return response()->json(['success' => false, 'message' => 'Unsupported flow'], 400);
    }

    public function history()
    {
        $payments = Auth::user()->payments()
            ->with(['order.product', 'paymentMethod'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function stripeWebhook(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Stripe disabled'], 410);
    }

    public function paypalWebhook(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'PayPal disabled'], 410);
    }

    public function fapshiWebhook(Request $request)
    {
        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!isset($payload['transId'])) {
            return response()->json(['success' => false], 400);
        }

        $fapshi = app(\App\Services\Payment\FapshiService::class);
        $event = $fapshi->paymentStatus($payload['transId']);

        $order = \App\Models\Order::where('payment_intent_id', $payload['transId'])->first();
        if (!$order) {
            return response()->json(['success' => false], 404);
        }

        switch ($event['status'] ?? null) {
            case 'SUCCESSFUL':
                $order->update([
                    'payment_status' => \App\Models\Order::PAYMENT_STATUS_PAID,
                    'status' => \App\Models\Order::STATUS_CONFIRMED,
                ]);
                if (class_exists(\App\Models\WalletTransaction::class)) {
                    \App\Models\WalletTransaction::where('trans_id', $payload['transId'])
                        ->update(['status' => 'completed']);
                }
                break;
            case 'FAILED':
                $order->update([
                    'payment_status' => \App\Models\Order::PAYMENT_STATUS_FAILED,
                ]);
                if (class_exists(\App\Models\WalletTransaction::class)) {
                    \App\Models\WalletTransaction::where('trans_id', $payload['transId'])
                        ->update(['status' => 'failed']);
                }
                break;
            case 'EXPIRED':
                $order->update([
                    'payment_status' => \App\Models\Order::PAYMENT_STATUS_FAILED,
                ]);
                if (class_exists(\App\Models\WalletTransaction::class)) {
                    \App\Models\WalletTransaction::where('trans_id', $payload['transId'])
                        ->update(['status' => 'failed']);
                }
                break;
        }

        return response()->json(['success' => true]);
    }
}