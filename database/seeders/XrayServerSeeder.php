<?php

namespace Database\Seeders;

use App\Models\XrayServer;
use Illuminate\Database\Seeder;

class XrayServerSeeder extends Seeder
{
    public function run(): void
    {
        $this->createVlessServer();
        // $this->createVmssServer();  // раскомментируй, если нужен VMess
    }

    protected function createVlessServer(): void
    {
        $server = XrayServer::updateOrCreate(
            ['host' => env('XRAY_VLESS_HOST')],
            [
                'name' => 'Reality Node',
                'host' => env('XRAY_VLESS_HOST'),
                'port' => (int) env('XRAY_VLESS_PORT', 443),

                'api_host' => '127.0.0.1',
                'api_port' => (int) env('XRAY_VLESS_API_PORT', 10085),

                'protocol' => 'vless',
                'inbound_tag' => env('XRAY_VLESS_INBOUND_TAG', 'vless-inbound'),

                'network' => 'tcp',
                'security' => 'reality',
                'flow' => env('XRAY_VLESS_FLOW', 'xtls-rprx-vision'),

                'reality_dest' => env('XRAY_VLESS_REALITY_DEST', 'dl.google.com:443'),
                'reality_server_names' => [env('XRAY_VLESS_REALITY_SNI', 'dl.google.com')],
                'reality_private_key' => env('XRAY_VLESS_REALITY_PRIVATE_KEY'),
                'reality_public_key' => env('XRAY_VLESS_REALITY_PUBLIC_KEY'),
                'reality_short_ids' => [env('XRAY_VLESS_REALITY_SHORT_ID', '0123456789abcdef')],
                'fingerprint' => env('XRAY_VLESS_FINGERPRINT', 'chrome'),

                'api_token' => XrayServer::generateApiToken(),
                'is_active' => true,
                'status' => 'unknown',
            ]
        );

        $this->command->info('VLESS Server ID: ' . $server->id);
        $this->command->warn('API Token: ' . $server->api_token);
    }

    protected function createVmssServer(): void
    {
        $server = XrayServer::updateOrCreate(
            ['host' => env('XRAY_VMESS_HOST')],
            [
                'name' => 'VMess Node',
                'host' => env('XRAY_VMESS_HOST'),
                'port' => (int) env('XRAY_VMESS_PORT', 80),

                'api_host' => '127.0.0.1',
                'api_port' => (int) env('XRAY_VMESS_API_PORT', 10085),

                'protocol' => 'vmess',
                'inbound_tag' => env('XRAY_VMESS_INBOUND_TAG', 'vmess-inbound'),

                'network' => env('XRAY_VMESS_NETWORK', 'ws'),
                'security' => 'none',
                'flow' => null,

                'ws_path' => env('XRAY_VMESS_PATH', '/api/v2/download'),
                'alter_id' => (int) env('XRAY_VMESS_ALTER_ID', 0),

                'api_token' => XrayServer::generateApiToken(),
                'is_active' => true,
                'status' => 'unknown',
            ]
        );

        $this->command->info('VMess Server ID: ' . $server->id);
        $this->command->warn('API Token: ' . $server->api_token);
    }
}