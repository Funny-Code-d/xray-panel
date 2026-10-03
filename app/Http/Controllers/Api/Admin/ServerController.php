<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\XrayServer;
use App\Models\XrayServerProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServerController extends Controller
{
    public function index(): JsonResponse
    {
        $servers = XrayServer::query()
            ->with('protocols')
            ->withCount('vpnClients')
            ->orderBy('name')
            ->get();

        return response()->json($servers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateServer($request);

        $server = DB::transaction(function () use ($validated) {
            $server = XrayServer::create([
                'name' => $validated['name'],
                'host' => $validated['host'],
                'api_host' => $validated['api_host'] ?? '127.0.0.1',
                'api_port' => $validated['api_port'] ?? 10085,
                'agent_port' => $validated['agent_port'] ?? 8080,
                'country' => $validated['country'] ?? null,
                'country_name' => $validated['country_name'] ?? null,
                'city' => $validated['city'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'api_token' => XrayServer::generateApiToken(),
                'status' => 'unknown',
            ]);

            $this->syncProtocols($server, $validated['protocols']);

            return $server;
        });

        return response()->json($server->load('protocols'), 201);
    }

    public function show(XrayServer $server): JsonResponse
    {
        $server->load('protocols')->loadCount('vpnClients');

        return response()->json($server);
    }

    public function update(Request $request, XrayServer $server): JsonResponse
    {
        $validated = $this->validateServer($request, updating: true);

        DB::transaction(function () use ($server, $validated) {
            // Обновляем общие поля
            $server->update(collect($validated)->except('protocols')->toArray());

            // Обновляем протоколы, если пришли
            if (array_key_exists('protocols', $validated)) {
                $this->syncProtocols($server, $validated['protocols']);
            }
        });

        return response()->json($server->fresh('protocols')->loadCount('vpnClients'));
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

    public function rotateToken(XrayServer $server): JsonResponse
    {
        $server->update(['api_token' => XrayServer::generateApiToken()]);

        return response()->json(['api_token' => $server->api_token]);
    }

    /**
     * Валидация сервера и его протоколов.
     */
    private function validateServer(Request $request, bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return $request->validate([
            // Общие поля
            'name' => [$required, 'string', 'max:100'],
            'host' => [$required, 'string', 'max:255'],
            'api_host' => ['nullable', 'string', 'max:255'],
            'api_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'agent_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'country' => ['nullable', 'string', 'size:2'],
            'country_name' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],

            // Протоколы
            'protocols' => [$required, 'array', 'min:1'],
            'protocols.*.protocol' => ['required', Rule::in(['vless', 'vmess', 'trojan'])],
            'protocols.*.is_enabled' => ['sometimes', 'boolean'],
            'protocols.*.port' => ['required', 'integer', 'min:1', 'max:65535'],
            'protocols.*.tag' => ['required', 'string', 'max:64'],
            'protocols.*.settings' => ['nullable', 'array'],

            // VLESS settings
            'protocols.*.settings.sni' => ['nullable', 'string', 'max:255'],
            'protocols.*.settings.dest' => ['nullable', 'string', 'max:255'],
            'protocols.*.settings.private_key' => ['nullable', 'string', 'max:255'],
            'protocols.*.settings.public_key' => ['nullable', 'string', 'max:255'],
            'protocols.*.settings.short_id' => ['nullable', 'string', 'max:32'],
            'protocols.*.settings.flow' => ['nullable', 'string', 'max:50'],
            'protocols.*.settings.fingerprint' => ['nullable', 'string', 'max:50'],

            // VMess settings
            'protocols.*.settings.path' => ['nullable', 'string', 'max:255'],

            // Trojan settings
            'protocols.*.settings.password' => ['nullable', 'string', 'min:16', 'max:255'],
        ]);
    }

    /**
     * Синхронизация протоколов сервера.
     */
    private function syncProtocols(XrayServer $server, array $protocols): void
    {
        $incoming = collect($protocols)->keyBy('protocol');

        // Удаляем протоколы, которых нет в запросе
        $server->protocols()
            ->whereNotIn('protocol', $incoming->keys())
            ->delete();

        // Upsert по protocol
        foreach ($incoming as $protocolData) {
            $server->protocols()->updateOrCreate(
                ['protocol' => $protocolData['protocol']],
                [
                    'is_enabled' => $protocolData['is_enabled'] ?? true,
                    'port' => $protocolData['port'],
                    'tag' => $protocolData['tag'],
                    'settings' => $protocolData['settings'] ?? [],
                ]
            );
        }
    }
}