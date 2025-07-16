<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/reports', function () {
    return Inertia::render('Reports');
})->middleware(['auth', 'verified'])->name('reports');

Route::get('/admin', function () {
    return Inertia::render('Admin');
})->middleware(['auth', 'verified'])->name('admin');

Route::get('/order-status', function () {
    return Inertia::render('OrderStatus');
})->middleware(['auth', 'verified'])->name('order-status');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/orders', [App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/api/orders/{id}', [App\Http\Controllers\OrderController::class, 'show']);
    Route::post('/api/orders', [App\Http\Controllers\OrderController::class, 'store']);
    Route::put('/api/orders/{id}', [App\Http\Controllers\OrderController::class, 'update']);
    Route::delete('/api/orders/{id}', [App\Http\Controllers\OrderController::class, 'destroy']);
    Route::patch('/api/orders/{id}/status', [App\Http\Controllers\OrderController::class, 'updateStatus']);

    Route::get('/api/order-statuses', [App\Http\Controllers\OrderStatusController::class, 'index']);
    Route::post('/api/order-statuses', [App\Http\Controllers\OrderStatusController::class, 'store']);
    Route::delete('/api/order-statuses/{id}', [App\Http\Controllers\OrderStatusController::class, 'destroy']);

    Route::post('/api/orders/{orderId}/travel', [App\Http\Controllers\TravelController::class, 'store']);

    Route::get('/api/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::post('/api/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('/api/users/{id}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/api/users/{id}', [App\Http\Controllers\UserController::class, 'destroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
