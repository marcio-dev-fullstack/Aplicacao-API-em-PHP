<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidTransactionException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (InsufficientBalanceException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'INSUFFICIENT_BALANCE',
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (InvalidTransactionException $e, Request $request) {
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_TRANSACTION',
                'message' => $e->getMessage(),
            ], 400);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'O recurso solicitado não foi encontrado.',
                ], 404);
            }
        });
    })->create();
