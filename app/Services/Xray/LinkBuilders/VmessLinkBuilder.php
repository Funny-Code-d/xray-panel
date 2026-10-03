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

        // $payload = [
        //     'v' => 2,
        //     'ps' => $client->name ?: $server->name,
        //     'add' => $server->host,
        //     'port' => (int) $protocol->port,
        //     'id' => $client->uuid,
        //     'aid' => 0,
        //     'scy' => 'auto',
        //     'net' => 'ws',
        //     'path' => $protocol->getSetting('path', '/vmess'),
        //     'type' => 'none',
        //     'host' => '',
            
        //     'tls' => 'tls',
        //     'sni' => $server->host,
        // ];

        // $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // $encoded = base64_encode($json);

        // return "vmess://{$encoded}";

        $params = http_build_query([
            'type'     => 'ws',
            'path'     => $protocol->getSetting('path', '/'),
            'host'     => $server->host,
            'security' => 'tls',
            'sni'      => $server->host,
        ], '', '&', PHP_QUERY_RFC3986);

        $fragment = rawurlencode($client->name ?: $server->name);

        return "vmess://{$client->uuid}@{$server->host}:{$protocol->port}?{$params}#{$fragment}";
    }
}