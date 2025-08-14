<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;

// Serve static files from storage (images, etc.) - MUST be before catch-all route
Route::get('storage/{path}', [FileController::class, 'serve'])->where('path', '.*');

// All other routes are handled by Vue Router
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
