<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use Illuminate\Support\Facades\Route;

// Публичные
Route::post('/login', [AuthController::class, 'login']);

// Требуют аутентификации
Route::middleware('auth:sanctum')->group(function () {
    // Аутентификация
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // VPN-клиенты (доступны всем аутентифицированным)
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::patch('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);

    // Только для админов (пример на будущее)
    Route::middleware('admin')->group(function () {
        // Здесь будут админские роуты: список всех пользователей, управление ролями и т.д.
    });
});