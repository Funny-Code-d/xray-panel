<?php

namespace App\Services\Xray;

use App\Models\XrayServer;
use App\Services\Xray\InboundBuilders\Contracts\InboundBuilder;
use App\Services\Xray\InboundBuilders\VlessBuilder;
use App\Services\Xray\InboundBuilders\VmessBuilder;
use App\Services\Xray\InboundBuilders\TrojanBuilder;

class ConfigBuilder
{
    /** @var array<string, InboundBuilder> */
    private array $builders;

    public function __construct(
        VlessBuilder $vless,
        VmessBuilder $vmess,
        TrojanBuilder $trojan,
    ) {
        $this->builders = [
            'vless' => $vless,
            'vmess' => $vmess,
            'trojan' => $trojan,
        ];
    }

    public function build(XrayServer $server): array
    {
        $clients = $this->getClients($server);
        $inbounds = [];

        $inbounds[] = [
            'tag' => 'api',
            'listen' => '0.0.0.0',
            'port' => (int) $server->api_port,
            'protocol' => 'dokodemo-door',
            'settings' => [
                'address' => '127.0.0.1',
            ],
        ];

        foreach ($server->enabledProtocols as $config) {
            $builder = $this->builders[$config->protocol] ?? null;

            if ($builder === null) {
                continue;
            }

            $inbounds[] = $builder->build($config, $clients);
        }

        return [
            'log' => ['loglevel' => 'warning'],
            'api' => [
                'tag' => 'api',
                'services' => ['HandlerService', 'StatsService'],
            ],
            'stats' => (object) [],
            'policy' => [
                'levels' => (object) [
                    '0' => [
                        'statsUserUplink' => true,
                        'statsUserDownlink' => true,
                    ],
                ],
                'system' => [
                    'statsInboundUplink' => true,
                    'statsInboundDownlink' => true,
                ],
            ],
            'inbounds' => $inbounds,
            'outbounds' => [
                ['tag' => 'direct', 'protocol' => 'freedom'],
                ['tag' => 'blocked', 'protocol' => 'blackhole'],
            ],
            'routing' => [
                'rules' => [
                    ['type' => 'field', 'ip' => ['geoip:private'], 'outboundTag' => 'blocked'],
                    ['type' => 'field', 'protocol' => ['bittorrent'], 'outboundTag' => 'blocked'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{uuid: string, email: string, trojan_password: ?string}>
     */
    private function getClients(XrayServer $server): array
    {
        return $server->vpnClients()
            ->where('is_active', true)
            ->get()
            ->map(fn ($c) => [
                'uuid' => $c->uuid,
                'email' => $c->email,
                'trojan_password' => $c->trojan_password ?? null,
            ])
            ->toArray();
    }
}