<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderStatusController;

use App\Http\Controllers\OrderController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/order-statuses', [OrderStatusController::class, 'index']);
    Route::post('/order-statuses', [OrderStatusController::class, 'store']);
    Route::delete('/order-statuses/{id}', [OrderStatusController::class, 'destroy']);

    // Rotas de pedidos (orders)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
});
