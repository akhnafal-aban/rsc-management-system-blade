<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('google', [AuthController::class, 'loginWithGoogle']);
    Route::post('claim/validate-member-code', [AuthController::class, 'validateMemberCode']);
    Route::post('claim/complete', [AuthController::class, 'completeClaim']);
});

Route::get('dashboard', [DashboardController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});


