<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::get('/{account}/balance', [AccountController::class, 'balance']);
        Route::get('/{account}/statement', [AccountController::class, 'statement']);
    });

    Route::prefix('transactions')->middleware('throttle:30,1')->group(function () {
        Route::post('/transfer', [TransactionController::class, 'transfer']);
        Route::post('/deposit', [TransactionController::class, 'deposit']);
        Route::get('/{transaction}', [TransactionController::class, 'show']);
    });
});
