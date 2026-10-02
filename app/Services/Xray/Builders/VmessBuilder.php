<?php

namespace App\Services\Xray\Builders;

use App\Models\XrayServerProtocol;
use App\Services\Xray\Builders\Contracts\InboundBuilder;

class VmessBuilder implements InboundBuilder
{
    public function build(XrayServerProtocol $config, array $clients): array
    {
        $server = $config->server;
        $settings = $config->settings;

        return [
            'tag' => $config->tag,
            'port' => (int) $config->port,
            'listen' => '0.0.0.0',
            'protocol' => 'vmess',
            'settings' => [
                'clients' => $this->buildClients($clients),
            ],
            'streamSettings' => [
                'network' => 'ws',
                'security' => 'tls',
                'tlsSettings' => [
                    'serverName' => $server->domain,
                    'certificates' => [[
                        'certificateFile' => '/etc/xray/cert.pem',
                        'keyFile' => '/etc/xray/key.pem',
                    ]],
                ],
                'wsSettings' => [
                    'path' => $settings['path'] ?? '/vmess',
                ],
            ],
            'sniffing' => [
                'enabled' => true,
                'destOverride' => ['http', 'tls', 'quic'],
            ],
        ];
    }

    private function buildClients(array $clients): array
    {
        return array_map(fn ($c) => [
            'id' => $c['uuid'],
            'alterId' => 0,
            'email' => $c['email'] . '@vmess',
        ], $clients);
    }
}