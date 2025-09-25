<?php

use Illuminate\Support\Facades\Route;
use Larashield\Http\Controllers\AuditLogController;
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
        Route::get('/profile', [AuthController::class, 'userProfile']);
        Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('permission-groups', PermissionController::class)->only(['index', 'store', 'show', 'destroy'])->middleware(['auth:sanctum']);
        Route::apiResource('roles', RoleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware(['auth:sanctum']);
    });
});
