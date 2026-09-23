<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\XrayServer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServerController extends Controller
{
    public function index(): JsonResponse
    {
        $servers = XrayServer::query()
            ->withCount('vpnClients')
            ->orderBy('name')
            ->get();

        return response()->json($servers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'api_host' => ['nullable', 'string', 'max:255'],
            'api_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'agent_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'protocol' => ['required', Rule::in(['vless', 'vmess'])],
            'inbound_tag' => ['required', 'string', 'max:100'],
            'network' => ['required', Rule::in(['tcp', 'ws', 'grpc'])],
            'security' => ['required', Rule::in(['none', 'tls', 'reality'])],
            'flow' => ['nullable', 'string', 'max:50'],
            'reality_dest' => ['nullable', 'string', 'max:255'],
            'reality_server_names' => ['nullable', 'array'],
            'reality_server_names.*' => ['string', 'max:255'],
            'reality_private_key' => ['nullable', 'string', 'max:255'],
            'reality_public_key' => ['nullable', 'string', 'max:255'],
            'reality_short_ids' => ['nullable', 'array'],
            'reality_short_ids.*' => ['string', 'max:32'],
            'fingerprint' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'size:2'],
            'country_name' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Генерируем токен, если не передан
        if (empty($validated['api_token'])) {
            $validated['api_token'] = XrayServer::generateApiToken();
        }

        $server = XrayServer::create($validated);

        return response()->json($server, 201);
    }

    public function show(XrayServer $server): JsonResponse
    {
        $server->loadCount('vpnClients');
        return response()->json($server);
    }

    public function update(Request $request, XrayServer $server): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'host' => ['sometimes', 'string', 'max:255'],
            'port' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'api_host' => ['nullable', 'string', 'max:255'],
            'api_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'agent_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'protocol' => ['sometimes', Rule::in(['vless', 'vmess'])],
            'inbound_tag' => ['sometimes', 'string', 'max:100'],
            'network' => ['sometimes', Rule::in(['tcp', 'ws', 'grpc'])],
            'security' => ['sometimes', Rule::in(['none', 'tls', 'reality'])],
            'flow' => ['nullable', 'string', 'max:50'],
            'reality_dest' => ['nullable', 'string', 'max:255'],
            'reality_server_names' => ['nullable', 'array'],
            'reality_private_key' => ['nullable', 'string', 'max:255'],
            'reality_public_key' => ['nullable', 'string', 'max:255'],
            'reality_short_ids' => ['nullable', 'array'],
            'fingerprint' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'size:2'],
            'country_name' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $server->update($validated);

        return response()->json($server->fresh());
    }

    public function destroy(XrayServer $server): JsonResponse
    {
        if ($server->vpnClients()->count() > 0) {
            return response()->json([
                'message' => 'Нельзя удалить сервер с активными ключами.',
            ], 422);
        }

        $server->delete();

        return response()->json(['message' => 'Сервер удалён.']);
    }
}