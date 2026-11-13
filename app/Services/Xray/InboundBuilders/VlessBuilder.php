<?php

namespace App\Services\Xray\InboundBuilders;

use App\Models\XrayServerProtocol;
use App\Services\Xray\InboundBuilders\Contracts\InboundBuilder;

class VlessBuilder implements InboundBuilder
{
    public function build(XrayServerProtocol $config, array $clients): array
    {
        $settings = $config->settings;

        return [
            'tag' => $config->tag,
            'port' => (int) $config->port,
            'listen' => '0.0.0.0',
            'protocol' => 'vless',
            'settings' => [
                'clients' => $this->buildClients($clients, $settings),
                'decryption' => 'none',
            ],
            'streamSettings' => [
                'network' => 'xhttp',
                'security' => 'reality',
                'realitySettings' => [
                    'show' => false,
                    'dest' => $settings['dest'],
                    'xver' => 0,
                    'serverNames' => [$settings['sni']],
                    'privateKey' => $settings['private_key'],
                    'shortIds' => [$settings['short_id']],
                ],
                // Добавляем настройки XHTTP
                'xhttpSettings' => [
                    'mode' => 'stream-one',
                    'path' => '/api/v1/updates',
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
        $flow = $settings['flow'] ?? 'xtls-rprx-vision';

        return array_map(fn ($c) => [
            'id' => $c['uuid'],
            'flow' => $flow,
            'email' => $c['email'],
        ], $clients);
    }
}