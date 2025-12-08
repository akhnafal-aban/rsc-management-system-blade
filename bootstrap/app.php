<?php

use App\Http\Middleware\RequireShiftConfirmation;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'shift.confirm' => RequireShiftConfirmation::class,
            'role' => RoleMiddleware::class, // Tambahkan ini
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom error page rendering (hanya untuk production)
        $exceptions->render(function (Throwable $e, Request $request) {
            if (config('app.debug') === true) {
                throw $e; // Paksa Laravel memproses error asli sepenuhnya
            }
        
            if ($request->is('api/*')) {
                return null;
            }

            $statusCode = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $statusCode = $e->getStatusCode();
            } elseif (method_exists($e, 'getCode') && $e->getCode() >= 400 && $e->getCode() < 600) {
                $statusCode = $e->getCode();
            }

            // Map status codes to error views
            $errorViews = [
                400 => 'errors.400',
                403 => 'errors.403',
                404 => 'errors.404',
                419 => 'errors.419',
                429 => 'errors.429',
                500 => 'errors.500',
                503 => 'errors.503',
            ];

            if (isset($errorViews[$statusCode])) {
                return response()->view($errorViews[$statusCode], [
                    'exception' => $e,
                ], $statusCode);
            }

            // Fallback to generic error page
            return response()->view('errors.error', [
                'exception' => $e,
            ], $statusCode);
        });
    })->create();
