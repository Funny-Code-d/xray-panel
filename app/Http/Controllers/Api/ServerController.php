<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\XrayServer;
use Illuminate\Http\JsonResponse;

class ServerController extends Controller
{
    public function index(): JsonResponse
    {
        $servers = XrayServer::query()
            ->where('is_active', true)
            ->with(['protocols', 'enabledProtocols'])
            ->withCount('vpnClients')
            ->orderBy('name')
            ->get()
            ->map(fn ($server) => [
                'id' => $server->id,
                'name' => $server->name,
                'host' => $server->host,
                'country' => $server->country,
                'country_name' => $server->country_name,
                'country_flag' => $server->country_flag,
                'city' => $server->city,
                'status' => $server->status ?? 'unknown',
                'last_seen_at' => $server->last_seen_at,
                'vpn_clients_count' => $server->vpn_clients_count,
                'protocols' => $server->protocols->map(fn ($p) => [
                    'protocol' => $p->protocol,
                    'is_enabled' => $p->is_enabled,
                    'port' => $p->port,
                ]),
            ]);

        return response()->json($servers);
    }

    public function publicIndex(): JsonResponse
    {
        $servers = XrayServer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($server) => [
                'id' => $server->id,
                'name' => $server->name,
                'country' => $server->country,
                'country_name' => $server->country_name,
                'country_flag' => $server->country_flag,
                'city' => $server->city,
                'status' => $server->status ?? 'unknown',
            ]);

        return response()->json($servers);
    }
}