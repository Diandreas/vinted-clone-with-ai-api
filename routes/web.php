<?php

use Illuminate\Support\Facades\Route;

// All routes are handled by Vue Router
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
