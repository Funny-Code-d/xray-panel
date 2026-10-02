<?php

namespace App\Services\Xray;

use App\Models\VpnClient;
use App\Models\XrayServerProtocol;
use App\Services\Xray\LinkBuilders\Contracts\LinkBuilder;
use App\Services\Xray\LinkBuilders\TrojanLinkBuilder;
use App\Services\Xray\LinkBuilders\VlessLinkBuilder;
use App\Services\Xray\LinkBuilders\VmessLinkBuilder;

class LinkGenerator
{
    /** @var array<string, LinkBuilder> */
    private array $builders;

    public function __construct(
        VlessLinkBuilder $vless,
        VmessLinkBuilder $vmess,
        TrojanLinkBuilder $trojan,
    ) {
        $this->builders = [
            'vless' => $vless,
            'vmess' => $vmess,
            'trojan' => $trojan,
        ];
    }

    /**
     * Построить ссылку для одного протокола.
     */
    public function build(XrayServerProtocol $protocol, VpnClient $client): ?string
    {
        $builder = $this->builders[$protocol->protocol] ?? null;

        return $builder?->build($protocol, $client);
    }

    /**
     * Построить ссылки для всех включённых протоколов сервера клиента.
     *
     * @return array<string, string> ['vless' => 'vless://...', 'vmess' => 'vmess://...']
     */
    public function buildAllForClient(VpnClient $client): array
    {
        $server = $client->xrayServer;

        if (!$server) {
            return [];
        }

        $server->load('enabledProtocols');

        $result = [];
        foreach ($server->enabledProtocols as $protocol) {
            $link = $this->build($protocol, $client);
            if ($link) {
                $result[$protocol->protocol] = $link;
            }
        }

        return $result;
    }
}