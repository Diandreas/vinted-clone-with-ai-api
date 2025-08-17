<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth('sanctum')->user();
        
        // Get session messages for payment redirects
        $messages = [];
        if (session('success')) {
            $messages['success'] = session('success');
        }
        if (session('error')) {
            $messages['error'] = session('error');
        }
        if (session('warning')) {
            $messages['warning'] = session('warning');
        }
        
        return Inertia::render('Payment/Index', [
            'auth' => [
                'user' => $user
            ],
            'paypalConfig' => [
                'clientId' => config('services.paypal.client_id', env('PAYPAL_CLIENT_ID'))
            ],
            'messages' => $messages
        ]);
    }

    /**
     * Handle payment redirects without authentication
     * This method is called when users are redirected from payment providers
     */
    public function handlePaymentRedirect(Request $request)
    {
        // Get session messages for payment redirects
        $messages = [];
        if (session('success')) {
            $messages['success'] = session('success');
        }
        if (session('error')) {
            $messages['error'] = session('error');
        }
        if (session('warning')) {
            $messages['warning'] = session('warning');
        }

        // Redirect to wallet page with messages
        return redirect('/wallet')->with($messages);
    }
}
