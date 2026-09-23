<?php

namespace App\Services;

use App\Models\XrayServer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XrayAgentService
{
    public function restartXray(XrayServer $server): bool
    {

        if (!$server->agent_port) {
            Log::warning('Agent port not set for server', ['server_id' => $server->id]);
            return false;
        }
        $url = "http://{$server->host}:{$server->agent_port}/restart-xray";

        try {
            $response = Http::withToken($server->api_token)
                ->timeout(10)
                ->post($url);

            if ($response->successful()) {
                Log::info('Xray restarted via agent', ['server_id' => $server->id]);
                return true;
            }

            Log::error('Agent returned error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'server_id' => $server->id,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to call agent', [
                'error' => $e->getMessage(),
                'server_id' => $server->id,
            ]);

            return false;
        }
    }
}