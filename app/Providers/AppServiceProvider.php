<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Password::createUrlUsing(function ($user, string $token) {
            $baseUrl = config('app.url');
            return $baseUrl . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
