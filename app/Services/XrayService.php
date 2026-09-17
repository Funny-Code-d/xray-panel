<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class XrayService
{
    protected string $apiServer;
    protected string $binary;

    public function __construct()
    {
        $this->apiServer = config('services.xray.api_server', '127.0.0.1:10085');
        $this->binary = config('services.xray.binary', 'xray');
    }

    /**
     * Проверить доступность API.
     */
    public function ping(): bool
    {
        try {
            $this->queryStats();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Получить всю статистику из Xray.
     * Возвращает массив: ['user>>>email>>>traffic>>>uplink' => 12345, ...]
     */
    public function getStats(): array
    {
        $data = $this->queryStats();

        if (!isset($data['stat']) || !is_array($data['stat'])) {
            return [];
        }

        $stats = [];
        foreach ($data['stat'] as $item) {
            if (!isset($item['name'])) {
                continue;
            }

            $name = $item['name'];

            // Xray не возвращает "value", если значение 0
            $value = $item['value'] ?? 0;

            // На случай вложенного объекта в новых версиях
            if (is_array($value)) {
                $value = $value['value'] ?? 0;
            }

            $stats[$name] = (int) $value;
        }

        return $stats;
    }

    /**
     * Получить трафик конкретного пользователя по email.
     * Возвращает ['uplink' => int, 'downlink' => int, 'total' => int].
     */
    public function getUserTraffic(string $email): array
    {
        $stats = $this->getStats();

        $uplink = $stats["user>>>{$email}>>>traffic>>>uplink"] ?? 0;
        $downlink = $stats["user>>>{$email}>>>traffic>>>downlink"] ?? 0;

        return [
            'uplink' => $uplink,
            'downlink' => $downlink,
            'total' => $uplink + $downlink,
        ];
    }

    /**
     * Получить трафик по всем email сразу.
     * Возвращает: ['email@vpn.local' => ['uplink' => int, 'downlink' => int, 'total' => int], ...]
     */
    public function getAllUsersTraffic(): array
    {
        $stats = $this->getStats();
        $result = [];

        foreach ($stats as $key => $value) {
            // Ключ: user>>>email>>>traffic>>>uplink|downlink
            if (!preg_match('/^user>>>(.+?)>>>traffic>>>(uplink|downlink)$/', $key, $matches)) {
                continue;
            }

            $email = $matches[1];
            $direction = $matches[2];

            if (!isset($result[$email])) {
                $result[$email] = ['uplink' => 0, 'downlink' => 0, 'total' => 0];
            }

            $result[$email][$direction] = $value;
            $result[$email]['total'] += $value;
        }

        return $result;
    }

    /**
     * Сбросить статистику (для отладки).
     * ВНИМАНИЕ: обнуляет счётчики в Xray, после этого дельты считать нельзя.
     */
    public function resetStats(): bool
    {
        $result = Process::run([
            $this->binary,
            'api',
            'statsquery',
            '--server=' . $this->apiServer,
            '-reset',
        ]);

        return $result->successful();
    }

    /**
     * Выполнить запрос к API и вернуть распарсенный JSON.
     */
    protected function queryStats(): array
    {
        $result = Process::run([
            $this->binary,
            'api',
            'statsquery',
            '--server=' . $this->apiServer,
        ]);

        if ($result->failed()) {
            $error = $result->errorOutput() ?: $result->output();
            Log::error('Xray API error', [
                'command' => 'statsquery',
                'server' => $this->apiServer,
                'error' => $error,
            ]);

            throw new RuntimeException("Xray API error: {$error}");
        }

        $output = trim($result->output());

        if ($output === '') {
            return ['stat' => []];
        }

        $data = json_decode($output, true);

        if (!is_array($data)) {
            Log::error('Xray API returned invalid JSON', ['output' => $output]);
            throw new RuntimeException('Xray API returned invalid JSON');
        }

        return $data;
    }

    /**
     * Добавить пользователя в работающий Xray (zero-downtime).
     */
    public function addUser(string $inboundTag, string $uuid, string $email): bool
    {
        // Находим сервер по inbound_tag, чтобы узнать port/protocol
        $server = \App\Models\XrayServer::where('inbound_tag', $inboundTag)->first();

        if (!$server) {
            Log::error('Xray addUser: server not found for tag', ['tag' => $inboundTag]);
            return false;
        }

        $payload = [
            'inbounds' => [
                [
                    'tag' => $inboundTag,
                    'protocol' => $server->protocol,           // ← vless
                    'port' => $server->port,                   // ← 443
                    'listen' => '0.0.0.0',                     // ← обязательно
                    'settings' => [
                        'clients' => [
                            [
                                'id' => $uuid,
                                'flow' => $server->flow ?: 'xtls-rprx-vision',
                                'email' => $email,
                            ],
                        ],
                        'decryption' => 'none',                 // ← для VLESS
                    ],
                ],
            ],
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $result = Process::input($json)->run([
            $this->binary,
            'api', 'adi',
            '--server=' . $this->apiServer,
        ]);

        if ($result->failed()) {
            Log::error('Xray addUser failed', [
                'uuid' => $uuid,
                'email' => $email,
                'error' => $result->errorOutput() ?: $result->output(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Удалить пользователя из работающего Xray.
     */
    public function removeUser(string $inboundTag, string $email): bool
    {
        $server = \App\Models\XrayServer::where('inbound_tag', $inboundTag)->first();

        if (!$server) {
            return false;
        }

        $payload = [
            'inbounds' => [
                [
                    'tag' => $inboundTag,
                    'protocol' => $server->protocol,
                    'port' => $server->port,
                    'listen' => '0.0.0.0',
                    'settings' => [
                        'clients' => [
                            ['email' => $email],
                        ],
                    ],
                ],
            ],
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $result = Process::input($json)->run([
            $this->binary,
            'api', 'rmi',
            '--server=' . $this->apiServer,
        ]);

        return $result->successful();
    }
}