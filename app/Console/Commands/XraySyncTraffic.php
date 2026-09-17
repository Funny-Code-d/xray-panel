<?php

namespace App\Console\Commands;

use App\Models\VpnClient;
use App\Services\XrayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XraySyncTraffic extends Command
{
    protected $signature = 'xray:sync-traffic';
    protected $description = 'Синхронизировать трафик из Xray в БД';

    public function handle(XrayService $xray): int
    {
        $this->info('Запрос статистики из Xray...');

        try {
            $stats = $xray->getAllUsersTraffic();
        } catch (\Exception $e) {
            $this->error('Ошибка Xray API: ' . $e->getMessage());
            Log::error('xray:sync-traffic failed', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        if (empty($stats)) {
            $this->info('Статистики нет (никто не подключался)');
            return self::SUCCESS;
        }

        $this->info('Найдено пользователей: ' . count($stats));

        $today = now()->toDateString();
        $processed = 0;
        $skipped = 0;

        // Обрабатываем пачками, чтобы не грузить память
        VpnClient::query()
            ->whereNotNull('email')
            ->chunkById(100, function ($clients) use ($stats, $today, &$processed, &$skipped) {
                foreach ($clients as $client) {
                    $email = $client->email;

                    if (!isset($stats[$email])) {
                        $skipped++;
                        continue;
                    }

                    $current = $stats[$email];

                    $this->syncClient($client, $current, $today);
                    $processed++;
                }
            });

        $this->info("Обработано: {$processed}, пропущено: {$skipped}");

        return self::SUCCESS;
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