<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;

// Test route
Route::get('/test-products', [ProductController::class, 'index']);
