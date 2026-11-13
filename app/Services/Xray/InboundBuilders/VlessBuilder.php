<?php

namespace App\Services\Xray\InboundBuilders;

use App\Models\XrayServerProtocol;
use App\Services\Xray\InboundBuilders\Contracts\InboundBuilder;

class VlessBuilder implements InboundBuilder
{
    public function build(XrayServerProtocol $config, array $clients): array
    {
        $settings = $config->settings;

        // Гарантируем, что dest содержит порт (Xray требует host:port для Reality)
        $dest = $settings['dest'] ?? 'dl.google.com:443';
        if (!str_contains($dest, ':')) {
            $dest .= ':443';
        }

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
                    'dest' => $dest, // Используем исправленный dest с портом
                    'xver' => 0,
                    'serverNames' => [$settings['sni'] ?? 'dl.google.com'],
                    'privateKey' => $settings['private_key'],
                    'shortIds' => [$settings['short_id']],
                ],
                'xhttpSettings' => [
                    'mode' => 'stream-one',
                    'path' => '/api/v1/updates',
                    'host' => '', // Явно указываем host, чтобы избежать ошибок валидации
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
            'id' => $c['uuid'],
            'email' => $c['email'],
        ], $clients);
    }
}