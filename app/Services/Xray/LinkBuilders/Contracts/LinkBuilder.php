<?php

namespace App\Services\Xray\LinkBuilders\Contracts;

use App\Models\VpnClient;
use App\Models\XrayServerProtocol;

interface LinkBuilder
{
    /**
     * Имя протокола ('vless', 'vmess', 'trojan').
     */
    public function protocol(): string;

    /**
     * Строит ссылку для клиента на этом протоколе.
     */
    public function build(XrayServerProtocol $protocol, VpnClient $client): string;
}