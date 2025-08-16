<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index()
    {
        return Inertia::render('Payment/Index', [
            'auth' => [
                'user' => auth()->user()
            ],
            'paypalConfig' => [
                'clientId' => config('services.paypal.client_id', env('PAYPAL_CLIENT_ID'))
            ]
        ]);
    }
}