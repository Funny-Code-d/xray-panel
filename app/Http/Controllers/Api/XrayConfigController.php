<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\XrayServer;
use App\Services\Xray\ConfigBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XrayConfigController extends Controller
{
    public function __construct(
        private readonly ConfigBuilder $configBuilder,
    ) {}

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

        $server->load('enabledProtocols');

        $config = $this->configBuilder->build($server);

        return response()->json($config);
    }
}