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
            ->withCount('vpnClients')
            ->orderBy('name')
            ->get()
            ->map(fn ($server) => [
                'id' => $server->id,
                'name' => $server->name,
                'host' => $server->host,
                'port' => $server->port,
                'country' => $server->country,
                'country_name' => $server->country_name,
                'country_flag' => $server->country_flag,
                'city' => $server->city,
                'status' => $server->status ?? 'unknown',
                'last_seen_at' => $server->last_seen_at,
                'vpn_clients_count' => $server->vpn_clients_count,
            ]);

        return response()->json($servers);
    }
}