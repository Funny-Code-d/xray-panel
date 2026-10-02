<?php

namespace App\Services\Xray\LinkBuilders;

use App\Models\VpnClient;
use App\Models\XrayServerProtocol;
use App\Services\Xray\LinkBuilders\Contracts\LinkBuilder;

class VmessLinkBuilder implements LinkBuilder
{
    public function protocol(): string
    {
        return 'vmess';
    }

    public function build(XrayServerProtocol $protocol, VpnClient $client): string
    {
        $server = $protocol->server;

        $payload = [
            'v' => '2',
            'ps' => $client->name ?: $server->name,
            'add' => $server->host,
            'port' => (string) $protocol->port,
            'id' => $client->uuid,
            'aid' => '0',
            'scy' => 'auto',
            'net' => 'ws',
            'type' => 'none',
            'host' => $server->host,
            'path' => $protocol->getSetting('path', '/vmess'),
            'tls' => 'tls',
            'sni' => $server->host,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $encoded = base64_encode($json);

        return "vmess://{$encoded}";
    }
}