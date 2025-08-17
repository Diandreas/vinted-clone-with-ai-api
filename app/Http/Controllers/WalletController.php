<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
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
        
        return Inertia::render('Wallet', [
            'auth' => [
                'user' => $user
            ],
            'messages' => $messages
        ]);
    }
}
