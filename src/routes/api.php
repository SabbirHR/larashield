<?php

use Illuminate\Support\Facades\Route;
use Larashield\Http\Controllers\UserController;
use Larashield\Http\Controllers\AuthController;

Route::prefix('api/v1')->group(function() {
    Route::post('/login', [AuthController::class,'login'])->name('login');
    Route::post('/register', [AuthController::class, 'registration'])->name('registration');
    Route::post('/logout', [AuthController::class,'logout'])->middleware('auth:sanctum')->name('logout');

    Route::middleware('auth:sanctum')->group(function() {
        Route::apiResource('users', UserController::class);
    });
});
