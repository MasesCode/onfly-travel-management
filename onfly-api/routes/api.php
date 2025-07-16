<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\UserController;

Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/register', [ApiAuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/user', [ApiAuthController::class, 'user']);
    Route::put('/profile', [ApiAuthController::class, 'updateProfile']);
    Route::put('/profile/password', [ApiAuthController::class, 'updatePassword']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    Route::get('/order-statuses', [OrderStatusController::class, 'index']);
    Route::post('/order-statuses', [OrderStatusController::class, 'store']);
    Route::delete('/order-statuses/{id}', [OrderStatusController::class, 'destroy']);

    Route::post('/orders/{orderId}/travel', [TravelController::class, 'store']);
    Route::put('/orders/{orderId}/travel', [TravelController::class, 'update']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
