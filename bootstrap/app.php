<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ensure API routes are completely isolated
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Ensure web middleware only applies to web routes
        $middleware->web(append: [
            // Add any web-specific middleware here if needed
        ]);
        
        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        
        // Ensure no global middleware interferes with API routes
        $middleware->use([
            // Only essential global middleware here
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();