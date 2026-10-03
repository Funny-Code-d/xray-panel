<?php

namespace App\Services\Xray\InboundBuilders;

use App\Models\XrayServerProtocol;
use App\Services\Xray\InboundBuilders\Contracts\InboundBuilder;

class TrojanBuilder implements InboundBuilder
{
    public function build(XrayServerProtocol $config, array $clients): array
    {
        $server = $config->server;
        $settings = $config->settings;

        return [
            'tag' => $config->tag,
            'port' => (int) $config->port,
            'listen' => '0.0.0.0',
            'protocol' => 'trojan',
            'settings' => [
                'clients' => $this->buildClients($clients, $settings),
                'fallbacks' => [
                    ['dest' => 80, 'xver' => 0],
                ],
            ],
            'streamSettings' => [
                'network' => 'tcp',
                'security' => 'tls',
                'tlsSettings' => [
                    'serverName' => $server->host,
                    'certificates' => [[
                        'certificateFile' => '/etc/xray/cert.pem',
                        'keyFile' => '/etc/xray/key.pem',
                    ]],
                ],
            ],
            'sniffing' => [
                'enabled' => true,
                'destOverride' => ['http', 'tls', 'quic'],
            ],
        ];
    }

    private function buildClients(array $clients, array $settings): array
    {
        return array_map(fn ($c) => [
            'password' => $c['trojan_password'] ?? $settings['password'],
            'email' => $c['email'],
        ], $clients);
    }
}