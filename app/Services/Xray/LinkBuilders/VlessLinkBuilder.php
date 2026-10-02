<?php

namespace App\Services\Xray\LinkBuilders;

use App\Models\VpnClient;
use App\Models\XrayServerProtocol;
use App\Services\Xray\LinkBuilders\Contracts\LinkBuilder;

class VlessLinkBuilder implements LinkBuilder
{
    public function protocol(): string
    {
        return 'vless';
    }

    public function build(XrayServerProtocol $protocol, VpnClient $client): string
    {
        $server = $protocol->server;

        $params = [
            'type' => 'tcp',
            'security' => 'reality',
            'flow' => $protocol->getSetting('flow', 'xtls-rprx-vision'),
            'sni' => $protocol->getSetting('sni'),
            'fp' => $protocol->getSetting('fingerprint', 'firefox'),
            'pbk' => $protocol->getSetting('public_key'),
            'sid' => $protocol->getSetting('short_id'),
            'spx' => '/',
        ];

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $fragment = rawurlencode($client->name ?: $server->name);

        return "vless://{$client->uuid}@{$server->host}:{$protocol->port}?{$query}#{$fragment}";
    }
}