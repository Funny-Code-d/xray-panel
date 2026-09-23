<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VpnClient;
use App\Models\XrayServer;
use App\Services\XrayService;
use App\Services\XrayAgentService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ClientController extends Controller
{
    /**
     * Список ключей текущего пользователя.
     * Для админа — все ключи.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = VpnClient::query()->with([
            'user:id,first_name,last_name,email',
            'xrayServer',  // ← добавлено: нужно для vless_link
        ]);

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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'xray_server_id' => ['nullable', 'integer', 'exists:xray_servers,id'],
        ]);

        $targetUserId = $user->isAdmin() && !empty($validated['user_id'])
            ? $validated['user_id']
            : $user->id;

        // Выбор сервера
        if (!empty($validated['xray_server_id'])) {
            $server = XrayServer::where('is_active', true)
                ->find($validated['xray_server_id']);
        } else {
            $server = XrayServer::where('is_active', true)->first();
        }

        if (!$server) {
            return response()->json([
                'message' => 'Нет доступных Xray-серверов.',
            ], 503);
        }

        $client = VpnClient::create([
            'user_id' => $targetUserId,
            'xray_server_id' => $server->id,
            'name' => $validated['name'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $client->refresh();
        $client->load(['user:id,first_name,last_name,email', 'xrayServer']);

        // Hot-reload Xray через агента
        app(XrayAgentService::class)->restartXray($server);

        return response()->json($client, 201);
    }

    /**
     * Показать конкретный ключ.
     */
    public function show(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->load([
            'user:id,first_name,last_name,email',
            'xrayServer',  // ← добавлено
        ]);

        return response()->json($client);
    }

    /**
     * Обновить ключ.
     */
    public function update(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $wasActive = $client->is_active;
        $client->update($validated);

        if (isset($validated['is_active']) && $validated['is_active'] !== $wasActive) {
            $client->load('xrayServer');
            if ($client->xrayServer) {
                app(XrayAgentService::class)->restartXray($client->xrayServer);
            }
        }

        return response()->json($client);
    }

    /**
     * Удалить ключ.
     */
    public function destroy(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->load('xrayServer');

        if ($client->xrayServer) {
            app(XrayAgentService::class)->restartXray($client->xrayServer);
        }

        $client->delete();

        return response()->json(['message' => 'Ключ удалён.']);
    }

    /**
     * Получить конфиг для подключения.
     * Возвращает и vmess, и vless ссылки (если применимо).
     */
    public function config(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->load('xrayServer');

        $protocol = $client->xrayServer?->protocol ?? 'vmess';

        return response()->json([
            'client_id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'protocol' => $protocol,                              // ← vless | vmess
            'vmess_link' => $client->vmess_link,
            'vless_link' => $client->vless_link,
            'is_active' => $client->is_active,
            'expires_at' => $client->expires_at,
        ]);
    }

    /**
     * Проверка доступа к ключу.
     */
    private function authorizeAccess(Request $request, VpnClient $client): void
    {
        $user = $request->user();

        if (! $user->isAdmin() && $client->user_id !== $user->id) {
            abort(403, 'Доступ запрещён.');
        }
    }
}