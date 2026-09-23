<?php

namespace App\Console\Commands;

use App\Models\VpnClient;
use App\Models\XrayServer;
use App\Services\XrayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XraySyncTraffic extends Command
{
    protected $signature = 'xray:sync-traffic';
    protected $description = 'Синхронизировать трафик из Xray в БД';

    public function handle(): int
    {
        $servers = XrayServer::where('is_active', true)->get();

        if ($servers->isEmpty()) {
            $this->warn('Нет активных серверов');
            return self::SUCCESS;
        }

        $this->info("Серверов для синхронизации: {$servers->count()}");

        foreach ($servers as $server) {
            $this->syncServer($server);
        }

        return self::SUCCESS;
    }

    protected function syncServer(XrayServer $server): void
    {
        $this->info("Сервер: {$server->name} ({$server->host})");

        $xray = XrayService::forServer($server);

        try {
            $stats = $xray->getAllUsersTraffic();
        } catch (\Exception $e) {
            $this->error("  ✗ Xray недоступен: {$e->getMessage()}");
            Log::error('Xray sync failed', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
            ]);

            $server->update(['status' => 'offline']);
            return;
        }

        $server->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        $this->info('  ✓ Xray online, найдено клиентов: ' . count($stats));

        $today = now()->toDateString();
        $processed = 0;

        VpnClient::query()
            ->where('xray_server_id', $server->id)
            ->whereNotNull('email')
            ->chunkById(100, function ($clients) use ($stats, $today, &$processed) {
                foreach ($clients as $client) {
                    if (!isset($stats[$client->email])) {
                        continue;
                    }

                    $this->syncClientTraffic($client, $stats[$client->email], $today);
                    $processed++;
                }
            });

        $this->info("  Обработано клиентов: {$processed}");
    }

    /**
     * Синхронизировать одного клиента.
     */
    protected function syncClient(VpnClient $client, array $current, string $today): void
    {
        $currentUpload = $current['uplink'];
        $currentDownlink = $current['downlink'];

        // Считаем дельту
        $deltaUpload = $currentUpload - $client->xray_last_upload;
        $deltaDownlink = $currentDownlink - $client->xray_last_downlink;

        // Если Xray перезапускался — счётчики обнулились
        // В этом случае текущее значение меньше предыдущего, берём текущее как дельту
        if ($deltaUpload < 0) {
            $deltaUpload = $currentUpload;
        }
        if ($deltaDownlink < 0) {
            $deltaDownlink = $currentDownlink;
        }

        $deltaTotal = $deltaUpload + $deltaDownlink;

        DB::transaction(function () use ($client, $current, $today, $deltaUpload, $deltaDownlink, $deltaTotal) {
            // 1. Обновляем traffic_used у ключа (накопительное)
            $client->increment('traffic_used', $deltaTotal);

            // 2. Сохраняем текущее значение как "предыдущее" для следующего раза
            $client->update([
                'xray_last_upload' => $current['uplink'],
                'xray_last_downlink' => $current['downlink'],
                'last_synced_at' => now(),
            ]);

            // 3. Обновляем traffic_used у пользователя (денормализация)
            $client->user()->increment('traffic_used', $deltaTotal);

            // 4. Пишем/обновляем дневную статистику
            $stat = \App\Models\TrafficStat::firstOrNew([
                'vpn_client_id' => $client->id,
                'period_start' => $today,
            ]);

            $stat->user_id = $client->user_id;
            $stat->upload = ($stat->upload ?? 0) + $deltaUpload;
            $stat->downlink = ($stat->downlink ?? 0) + $deltaDownlink;
            $stat->total = ($stat->total ?? 0) + $deltaTotal;
            $stat->save();
        });
    }
}