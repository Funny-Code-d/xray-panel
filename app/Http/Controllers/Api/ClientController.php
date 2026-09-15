<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VpnClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Список ключей текущего пользователя.
     * Для админа — все ключи.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = VpnClient::query()->with('user:id,first_name,last_name,email');

        // Обычный пользователь видит только свои ключи
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Фильтр по активности (опционально)
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $clients = $query->latest()->paginate(20);

        return response()->json($clients);
    }

    /**
     * Создать новый ключ.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'user_id' => [
                // Обязательно только для админа, и только если он явно указал "другого" пользователя
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        // Если админ передал user_id — создаём для него. Иначе — для себя.
        $targetUserId = $user->isAdmin() && !empty($validated['user_id'])
            ? $validated['user_id']
            : $user->id;

        $client = VpnClient::create([
            'user_id' => $targetUserId,
            'name' => $validated['name'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);
        $client->refresh();
        $client->load('user:id,first_name,last_name,email');

        return response()->json($client, 201);
    }

    /**
     * Показать конкретный ключ.
     */
    public function show(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->load('user:id,first_name,last_name,email');

        return response()->json($client);
    }

    /**
     * Обновить ключ (например, переименовать или деактивировать).
     */
    public function update(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    /**
     * Удалить ключ.
     */
    public function destroy(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->delete();

        return response()->json(['message' => 'Ключ удалён.']);
    }

    /**
     * Проверка: имеет ли пользователь доступ к ключу.
     */
    private function authorizeAccess(Request $request, VpnClient $client): void
    {
        $user = $request->user();

        if (! $user->isAdmin() && $client->user_id !== $user->id) {
            abort(403, 'Доступ запрещён.');
        }
    }

    public function config(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        return response()->json([
            'client_id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'vmess_link' => $client->vmess_link,
            'is_active' => $client->is_active,
            'expires_at' => $client->expires_at,
        ]);
    }
}