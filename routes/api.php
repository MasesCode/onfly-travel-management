<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderStatusController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/order-statuses', [OrderStatusController::class, 'index']);
    Route::post('/order-statuses', [OrderStatusController::class, 'store']);
    Route::delete('/order-statuses/{id}', [OrderStatusController::class, 'destroy']);
});
