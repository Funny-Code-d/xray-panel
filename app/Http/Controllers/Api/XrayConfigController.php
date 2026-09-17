<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\XrayServer;
use App\Services\XrayConfigBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XrayConfigController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $server = XrayServer::where('api_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$server) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Обновляем heartbeat
        $server->update([
            'last_seen_at' => now(),
            'status' => 'online',
        ]);

        $config = (new XrayConfigBuilder($server))->build();

        return response()->json($config);
    }
}