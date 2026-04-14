<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('login')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('generate-otp', [AuthController::class, 'generateOTP']);
    Route::post('verify-otp', [AuthController::class, 'verifyOTP']);
});

Route::prefix('password')->group(function () {
    Route::post('verify-password', [AuthController::class, 'verifyPassword']);
    Route::middleware('auth:api')->post('change-password', [AuthController::class, 'changePassword']);
});