<?php

namespace App\Services;

use App\Models\XrayServer;

class XrayConfigBuilder
{
    public function __construct(
        protected XrayServer $server,
    ) {}

    /**
     * Собрать полный конфиг Xray для сервера.
     */
    public function build(): array
    {
        return [
            'log' => [
                'loglevel' => 'warning',
                'access' => '/var/log/xray/access.log',
                'error' => '/var/log/xray/error.log',
            ],
            'api' => $this->buildApi(),
            'stats' => new \stdClass(),
            'policy' => $this->buildPolicy(),
            'inbounds' => [$this->buildInbound()],
            'outbounds' => $this->buildOutbounds(),
            'routing' => $this->buildRouting(),
        ];
    }

    protected function buildApi(): array
    {
        return [
            'tag' => 'api',
            'listen' => "{$this->server->api_host}:{$this->server->api_port}",
            'services' => ['HandlerService', 'StatsService'],
        ];
    }

    protected function buildPolicy(): array
    {
        $levels = new \stdClass();
        $levels->{'0'} = [
            'statsUserUplink' => true,
            'statsUserDownlink' => true,
        ];

        return [
            'system' => [
                'statsInboundUplink' => true,
                'statsInboundDownlink' => true,
                'statsOutboundUplink' => true,
                'statsOutboundDownlink' => true,
            ],
            'levels' => $levels,
        ];
    }

    /**
     * Собрать inbound с активными клиентами.
     */
    protected function buildInbound(): array
    {
        $inbound = [
            'listen' => '0.0.0.0',
            'port' => $this->server->port,
            'protocol' => $this->server->protocol,
            'tag' => $this->server->inbound_tag,
            'settings' => $this->buildInboundSettings(),
            'streamSettings' => $this->buildStreamSettings(),
            'sniffing' => [
                'enabled' => true,
                'destOverride' => ['http', 'tls', 'quic'],
                'routeOnly' => false,
            ],
        ];

        return $inbound;
    }

    /**
     * Настройки inbound в зависимости от протокола.
     */
    protected function buildInboundSettings(): array
    {
        $clients = $this->server->vpnClients()
            ->where('is_active', true)
            ->get()
            ->map(fn ($client) => $this->buildClient($client))
            ->values()
            ->toArray();

        if ($this->server->protocol === 'vless') {
            return [
                'clients' => $clients,
                'decryption' => 'none',
            ];
        }

        // vmess
        return [
            'clients' => $clients,
        ];
    }

    /**
     * Настройки одного клиента.
     */
    protected function buildClient($client): array
    {
        if ($this->server->protocol === 'vless') {
            return [
                'id' => $client->uuid,
                'flow' => $this->server->flow ?: 'xtls-rprx-vision',
                'email' => $client->email,
            ];
        }

        // vmess
        return [
            'id' => $client->uuid,
            'alterId' => $this->server->alter_id ?? 0,
            'email' => $client->email,
        ];
    }

    /**
     * Настройки транспорта и безопасности.
     */
    protected function buildStreamSettings(): array
    {
        $settings = [
            'network' => $this->server->network,
            'security' => $this->server->security,
        ];

        // Reality
        if ($this->server->security === 'reality') {
            $settings['realitySettings'] = [
                'dest' => $this->server->reality_dest,
                'serverNames' => $this->server->reality_server_names,
                'privateKey' => $this->server->reality_private_key,
                'shortIds' => $this->server->reality_short_ids,
            ];
        }

        // TLS
        if ($this->server->security === 'tls') {
            $settings['tlsSettings'] = [
                'serverName' => $this->server->primary_server_name,
            ];
        }

        // WebSocket
        if ($this->server->network === 'ws' && $this->server->ws_path) {
            $settings['wsSettings'] = [
                'path' => $this->server->ws_path,
            ];
        }

        return $settings;
    }

    protected function buildOutbounds(): array
    {
        return [
            ['protocol' => 'freedom', 'tag' => 'direct'],
            ['protocol' => 'blackhole', 'tag' => 'block'],
        ];
    }

    protected function buildRouting(): array
    {
        return [
            'rules' => [
                [
                    'type' => 'field',
                    'inboundTag' => ['api'],
                    'outboundTag' => 'api',
                ],
                [
                    'type' => 'field',
                    'ip' => ['geoip:private'],
                    'outboundTag' => 'block',
                ],
            ],
        ];
    }
}