<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Role;

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
            'user' => $this->userPayload($user),
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

        return response()->json($this->userPayload($user));
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'email' => $validated['email'],
            'phone' => !empty($validated['phone']) ? $validated['phone'] : null,
            'password' => $validated['password'],
            'registration_date' => now(),
            'approval_status' => 'pending',
        ]);

        // Назначаем роль user
        $userRole = Role::where('code', 'user')->first();
        if ($userRole) {
            $user->roles()->attach($userRole->id);
        }

        // Создаём Sanctum-токен сразу
        $deviceName = $request->input('device_name', 'web');
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->load('roles');

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ], 201);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->roles->pluck('code'),
            'is_admin' => $user->isAdmin(),
            'approval_status' => $user->approval_status,
            'traffic_limit' => $user->traffic_limit,
            'traffic_used' => $user->total_traffic_used,
            'registration_date' => $user->registration_date,
            'last_auth_date' => $user->last_auth_date,
            'is_blocked' => $user->is_blocked,
            'block_reason' => $user->block_reason,
        ];
    }
}