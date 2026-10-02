<?php

namespace App\Services\Xray\Builders\Contracts;

use App\Models\XrayServerProtocol;

interface InboundBuilder
{
    /**
     * Собирает inbound для этого протокола.
     *
     * @param  XrayServerProtocol  $config   Настройки протокола (порт, тег, settings)
     * @param  array               $clients  Массив клиентов сервера
     * @return array               Xray inbound config
     */
    public function build(XrayServerProtocol $config, array $clients): array;
}