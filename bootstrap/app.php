<?php

use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // This explicitly configures CSRF token validation for web routes.
        // The `except` array should list URIs to exclude from CSRF protection.
        // For Backpack, this array should generally be empty or not include admin routes.
        $middleware->validateCsrfTokens(except: [
            // 'api/*', // Example: if your API routes are stateless and don't need CSRF
        ]);

        // Your existing CORS middleware (or other global middleware)
        $middleware->append(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
