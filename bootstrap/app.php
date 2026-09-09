<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function (): void {
            Route::middleware('api')
                ->prefix('api/landlord')
                ->name('landlord.')
                ->group(base_path('routes/landlord.php'));

            Route::middleware('api')
                ->prefix('api/business')
                ->name('business.')
                ->group(base_path('routes/business.php'));

            Route::middleware('api')
                ->prefix('api/patient')
                ->name('patient.')
                ->group(base_path('routes/patient.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed.',
                        'errors' => $e->errors(),
                    ], 422);
                }

                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated.',
                    ], 401);
                }

                if ($e instanceof AccessDeniedHttpException || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied.',
                    ], 403);
                }

                if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource not found.',
                    ], 404);
                }

                if ($e instanceof MethodNotAllowedHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'HTTP Method not allowed.',
                    ], 405);
                }

                // Log full exception details silently to daily log
                Log::error('API Exception Caught', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Return generic clean error response without exposing internal backend trace
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.',
                ], $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500);
            }
        });
    })->create();
