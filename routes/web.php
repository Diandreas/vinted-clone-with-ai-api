<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WalletController;

// Serve static files from storage (images, etc.) - MUST be before catch-all route
Route::get('storage/{path}', [FileController::class, 'serve'])->where('path', '.*');

// Payment routes (must be before catch-all)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('payment', [PaymentController::class, 'index'])->name('payment.index');
});

// Wallet route - accessible without authentication for payment redirects
Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');

// Public payment redirect route for handling payment callbacks without authentication
Route::get('payment/redirect', [PaymentController::class, 'handlePaymentRedirect'])->name('payment.redirect');

// NotchPay callback route (PUBLIC - must be before catch-all)
Route::get('payment/callback', [\App\Http\Controllers\NotchPayController::class, 'handleCallback'])->name('payment.callback');

// Payment result page (PUBLIC - for displaying payment results)
Route::get('payment/result', [\App\Http\Controllers\NotchPayController::class, 'showPaymentResult'])->name('payment.result');

// All other routes are handled by Vue Router (EXCEPT API routes)
Route::get('{any}', function () {
    // Skip API routes
    if (str_starts_with(request()->path(), 'api/')) {
        abort(404);
    }
    
    return view('app');
})->where('any', '.*');
