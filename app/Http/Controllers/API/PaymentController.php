<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

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
            'payment_method_id' => 'required|string', // Stripe Payment Method ID
            'is_default' => 'boolean'
        ]);

        try {
            $user = Auth::user();
            
            // Create or get Stripe customer
            if (!$user->stripe_customer_id) {
                $customer = Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
                $user->update(['stripe_customer_id' => $customer->id]);
            }

            // Attach payment method to customer
            $stripePaymentMethod = StripePaymentMethod::retrieve($request->payment_method_id);
            $stripePaymentMethod->attach(['customer' => $user->stripe_customer_id]);

            // Store in database
            $paymentMethod = $user->paymentMethods()->create([
                'stripe_payment_method_id' => $request->payment_method_id,
                'type' => $stripePaymentMethod->type,
                'last_four' => $stripePaymentMethod->card->last4 ?? null,
                'brand' => $stripePaymentMethod->card->brand ?? null,
                'expires_month' => $stripePaymentMethod->card->exp_month ?? null,
                'expires_year' => $stripePaymentMethod->card->exp_year ?? null,
                'is_default' => $request->is_default ?? false
            ]);

            // Set as default if requested
            if ($request->is_default) {
                $user->paymentMethods()->where('id', '!=', $paymentMethod->id)
                    ->update(['is_default' => false]);
            }

            return response()->json([
                'success' => true,
                'data' => $paymentMethod,
                'message' => 'Payment method added successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment method: ' . $e->getMessage()
            ], 400);
        }
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
        
        try {
            // Detach from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethod->stripe_payment_method_id);
            $stripePaymentMethod->detach();

            // Soft delete
            $paymentMethod->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method: ' . $e->getMessage()
            ], 400);
        }
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
            'payment_method_id' => 'nullable|exists:payment_methods,id'
        ]);

        $order = Order::findOrFail($request->order_id);
        
        // Verify user is the buyer
        if ($order->buyer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get payment method
        $paymentMethodId = $request->payment_method_id;
        if (!$paymentMethodId) {
            $paymentMethod = Auth::user()->paymentMethods()->where('is_default', true)->first();
            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payment method available'
                ], 400);
            }
            $paymentMethodId = $paymentMethod->id;
        }

        $paymentMethod = Auth::user()->paymentMethods()->findOrFail($paymentMethodId);

        try {
            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $order->total_amount * 100, // Convert to cents
                'currency' => 'eur',
                'customer' => Auth::user()->stripe_customer_id,
                'payment_method' => $paymentMethod->stripe_payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => config('app.url') . '/payment/return',
            ]);

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'payment_method_id' => $paymentMethod->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'amount' => $order->total_amount,
                'currency' => 'EUR',
                'status' => $paymentIntent->status,
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'payment' => $payment,
                    'client_secret' => $paymentIntent->client_secret,
                    'status' => $paymentIntent->status
                ],
                'message' => 'Payment processed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ], 400);
        }
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
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Exception $e) {
            return response('Webhook signature verification failed.', 400);
        }

        // Handle the event
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event['data']['object'];
                $payment = Payment::where('stripe_payment_intent_id', $paymentIntent['id'])->first();
                if ($payment) {
                    $payment->update(['status' => 'succeeded']);
                    $payment->order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
                }
                break;
            
            case 'payment_intent.payment_failed':
                $paymentIntent = $event['data']['object'];
                $payment = Payment::where('stripe_payment_intent_id', $paymentIntent['id'])->first();
                if ($payment) {
                    $payment->update(['status' => 'failed']);
                    $payment->order->update(['payment_status' => 'failed']);
                }
                break;
        }

        return response()->json(['success' => true]);
    }

    public function paypalWebhook(Request $request)
    {
        // PayPal webhook implementation would go here
        return response()->json(['success' => true]);
    }
}