<?php

use Illuminate\Support\Facades\Route;
use Larashield\Http\Controllers\AuthController;
use Larashield\Http\Controllers\PermissionController;
use Larashield\Http\Controllers\RoleController;
use Larashield\Http\Controllers\UserController;
use Spatie\Permission\Models\Role;

Route::prefix('api/v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'registration'])->name('registration');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::middleware('auth:sanctum')->group(function () {
        // Bind {role} to Role model
        Route::bind('role', function ($value) {
            return Role::findOrFail($value);
        });
        Route::apiResource('users', UserController::class);
        Route::apiResource('permission-groups', PermissionController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::apiResource('roles', RoleController::class);
    });
});
