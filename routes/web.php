<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PaymentController;

// Serve static files from storage (images, etc.) - MUST be before catch-all route
Route::get('storage/{path}', [FileController::class, 'serve'])->where('path', '.*');

// Payment routes (must be before catch-all)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::get('wallet', [PaymentController::class, 'index'])->name('wallet.index');
});

// NotchPay callback route (PUBLIC - must be before catch-all)
Route::get('payment/callback', [\App\Http\Controllers\NotchPayController::class, 'handleCallback'])->name('payment.callback.web');

// All other routes are handled by Vue Router
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
