<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use \App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\ClientController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\XrayConfigController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\TagController as AdminTagController;

/*
|--------------------------------------------------------------------------
| Публичные роуты (без аутентификации)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/xray/config', [XrayConfigController::class, 'show']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::get('/tags', [TagController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Требуют аутентификации (доступно всем, включая pending/rejected)
|--------------------------------------------------------------------------
|
| Здесь только то, что нужно для работы auth-цикла:
| - выход
| - получение текущего пользователя (чтобы фронт мог обновить статус)
|
*/

Route::middleware(['auth:sanctum', 'not_blocked'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::post('/subscriptions/bulk', [SubscriptionController::class, 'bulkUpdate']);
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Только для одобренных пользователей
|--------------------------------------------------------------------------
|
| Pending/rejected пользователи получат 403 от middleware 'approved'.
|
*/

Route::middleware(['auth:sanctum', 'not_blocked', 'approved'])->group(function () {
    // VPN-клиенты
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{client}/config', [ClientController::class, 'config']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::patch('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Только для администраторов
    |----------------------------------------------------------------------
    */

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::patch('/users/{user}', [AdminUserController::class, 'update']);
        Route::post('/users/{user}/approve', [AdminUserController::class, 'approve']);
        Route::post('/users/{user}/reject', [AdminUserController::class, 'reject']);
        Route::post('/users/{user}/block', [AdminUserController::class, 'block']);
        Route::post('/users/{user}/unblock', [AdminUserController::class, 'unblock']);
        Route::apiResource('posts', AdminPostController::class);
        Route::apiResource('tags', AdminTagController::class);
    });
});