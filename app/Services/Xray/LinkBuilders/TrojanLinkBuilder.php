<?php

namespace App\Services\Xray\LinkBuilders;

use App\Models\VpnClient;
use App\Models\XrayServerProtocol;
use App\Services\Xray\LinkBuilders\Contracts\LinkBuilder;

class TrojanLinkBuilder implements LinkBuilder
{
    public function protocol(): string
    {
        return 'trojan';
    }

    public function build(XrayServerProtocol $protocol, VpnClient $client): string
    {
        $server = $protocol->server;

        $password = $client->trojan_password ?: $protocol->getSetting('password');

        $params = [
            'security' => 'tls',
            'sni' => $server->host,
            'type' => 'tcp',
        ];

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $fragment = rawurlencode($client->name ?: $server->name);

        return "trojan://{$password}@{$server->host}:{$protocol->port}?{$query}#{$fragment}";
    }
}