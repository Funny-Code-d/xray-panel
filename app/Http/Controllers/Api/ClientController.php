<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VpnClient;
use App\Models\XrayServer;
use App\Services\Xray\LinkGenerator;
use App\Services\Xray\XrayAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private readonly LinkGenerator $linkGenerator,
        private readonly XrayAgentService $agentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = VpnClient::query()->with([
            'user:id,first_name,last_name,email',
            'xrayServer.protocols',
        ]);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $clients = $query->latest()->paginate(20);

        return response()->json($clients);
    }

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

        $server = !empty($validated['xray_server_id'])
            ? XrayServer::where('is_active', true)->find($validated['xray_server_id'])
            : XrayServer::where('is_active', true)->first();

        if (!$server) {
            return response()->json(['message' => 'Нет доступных Xray-серверов.'], 503);
        }

        $client = VpnClient::create([
            'user_id' => $targetUserId,
            'xray_server_id' => $server->id,
            'name' => $validated['name'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $client->refresh();
        $client->load(['user:id,first_name,last_name,email', 'xrayServer.protocols']);

        $this->agentService->restartXray($server);

        return response()->json($client, 201);
    }

    public function show(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->load(['user:id,first_name,last_name,email', 'xrayServer.protocols']);

        return response()->json($client);
    }

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
                $this->agentService->restartXray($client->xrayServer);
            }
        }

        return response()->json($client);
    }

    public function destroy(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->load('xrayServer');

        if ($client->xrayServer) {
            $this->agentService->restartXray($client->xrayServer);
        }

        $client->delete();

        return response()->json(['message' => 'Ключ удалён.']);
    }

    /**
     * Получить ссылки для подключения по всем включённым протоколам сервера.
     */
    public function config(Request $request, VpnClient $client): JsonResponse
    {
        $this->authorizeAccess($request, $client);

        $client->load('xrayServer.enabledProtocols');

        $links = $this->linkGenerator->buildAllForClient($client);

        return response()->json([
            'client_id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'uuid' => $client->uuid,
            'server' => [
                'id' => $client->xrayServer?->id,
                'name' => $client->xrayServer?->name,
                'host' => $client->xrayServer?->host,
                'domain' => $client->xrayServer?->domain,
            ],
            'links' => $links,
            'is_active' => $client->is_active,
            'expires_at' => $client->expires_at,
        ]);
    }

    private function authorizeAccess(Request $request, VpnClient $client): void
    {
        $user = $request->user();

        if (!$user->isAdmin() && $client->user_id !== $user->id) {
            abort(403, 'Доступ запрещён.');
        }
    }
}