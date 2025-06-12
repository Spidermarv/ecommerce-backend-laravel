<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController; // Corrected namespace
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store']); // Handles simulated order posting

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']); // Example protected route
});
