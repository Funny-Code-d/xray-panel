<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Логин: проверка credentials, выдача Sanctum-токена.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        // Проверяем: пользователь существует И пароль верный
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        // Обновляем дату последней аутентификации
        $user->update(['last_auth_date' => now()]);

        // Удаляем старые токены этого устройства (опционально)
        $deviceName = $request->input('device_name', 'web');
        $user->tokens()->where('name', $deviceName)->delete();

        // Создаём новый токен
        $token = $user->createToken($deviceName)->plainTextToken;

        // Загружаем роли, чтобы вернуть клиенту
        $user->load('roles');

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_name' => $user->middle_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->roles->pluck('code'),
                'is_admin' => $user->isAdmin(),
                'traffic_limit' => $user->traffic_limit,
                'traffic_used' => $user->total_traffic_used,
            ],
        ]);
    }

    /**
     * Логаут: удаление текущего токена.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Вы вышли из системы.']);
    }

    /**
     * Текущий пользователь.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->roles->pluck('code'),
            'is_admin' => $user->isAdmin(),
            'traffic_limit' => $user->traffic_limit,
            'traffic_used' => $user->total_traffic_used,
            'registration_date' => $user->registration_date,
            'last_auth_date' => $user->last_auth_date,
        ]);
    }
}