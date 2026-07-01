<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Order\CancelOrderController;
use App\Http\Controllers\Api\V1\Order\ConfirmOrderController;
use App\Http\Controllers\Api\V1\Order\OrderController;
use App\Http\Controllers\Api\V1\Payment\OrderPaymentController;
use App\Http\Controllers\Api\V1\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', RegisterController::class);
        Route::post('login', LoginController::class);
    });

    Route::middleware('auth:api')->group(function (): void {
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('orders/{order}/confirm', ConfirmOrderController::class);
        Route::post('orders/{order}/cancel', CancelOrderController::class);

        Route::get('orders/{order}/payments', OrderPaymentController::class);
        Route::get('payments', PaymentController::class);
    });
});
