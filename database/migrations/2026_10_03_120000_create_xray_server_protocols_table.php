<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Создаём таблицу протоколов
        Schema::create('xray_server_protocols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('xray_servers')->cascadeOnDelete();
            $table->string('protocol', 32);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('port');
            $table->string('tag', 64);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['server_id', 'protocol']);
            $table->unique(['server_id', 'port']);
        });

        // 2. Переносим существующие данные
        $servers = DB::table('xray_servers')->get();

        foreach ($servers as $server) {
            $settings = $this->buildSettingsFromServer($server);
            $tag = $this->buildTag($server);

            DB::table('xray_server_protocols')->insert([
                'server_id' => $server->id,
                'protocol' => $server->protocol,
                'is_enabled' => $server->is_active,
                'port' => $server->port,
                'tag' => $tag,
                'settings' => json_encode($settings),
                'created_at' => $server->created_at ?? now(),
                'updated_at' => $server->updated_at ?? now(),
            ]);
        }

        // 3. Удаляем старые колонки из xray_servers
        Schema::table('xray_servers', function (Blueprint $table) {
            $table->dropColumn([
                'port',
                'protocol',
                'inbound_tag',
                'network',
                'security',
                'flow',
                'alter_id',
                'reality_dest',
                'reality_server_names',
                'reality_private_key',
                'reality_public_key',
                'reality_short_ids',
                'fingerprint',
                'ws_path',
            ]);
        });
    }

    public function down(): void
    {
        // 1. Возвращаем колонки в xray_servers
        Schema::table('xray_servers', function (Blueprint $table) {
            $table->unsignedInteger('port')->default(443);
            $table->string('protocol')->default('vless');
            $table->string('inbound_tag')->default('vless-inbound');
            $table->string('network')->default('tcp');
            $table->string('security')->default('reality');
            $table->string('flow')->nullable();
            $table->unsignedInteger('alter_id')->default(0);
            $table->string('reality_dest')->nullable();
            $table->json('reality_server_names')->nullable();
            $table->string('reality_private_key')->nullable();
            $table->string('reality_public_key')->nullable();
            $table->json('reality_short_ids')->nullable();
            $table->string('fingerprint')->default('chrome');
            $table->string('ws_path')->nullable();
        });

        // 2. Возвращаем данные (берём первый протокол каждого сервера)
        $protocols = DB::table('xray_server_protocols')->get()->groupBy('server_id');

        foreach ($protocols as $serverId => $items) {
            $primary = $items->first();
            $settings = json_decode($primary->settings ?? '{}', true) ?: [];

            DB::table('xray_servers')
                ->where('id', $serverId)
                ->update([
                    'port' => $primary->port,
                    'protocol' => $primary->protocol,
                    'inbound_tag' => $primary->tag,
                    'network' => $settings['network'] ?? 'tcp',
                    'security' => $settings['security'] ?? 'reality',
                    'flow' => $settings['flow'] ?? null,
                    'alter_id' => $settings['alter_id'] ?? 0,
                    'reality_dest' => $settings['reality_dest'] ?? null,
                    'reality_server_names' => json_encode($settings['reality_server_names'] ?? []),
                    'reality_private_key' => $settings['reality_private_key'] ?? null,
                    'reality_public_key' => $settings['reality_public_key'] ?? null,
                    'reality_short_ids' => json_encode($settings['reality_short_ids'] ?? []),
                    'fingerprint' => $settings['fingerprint'] ?? 'chrome',
                    'ws_path' => $settings['ws_path'] ?? null,
                ]);
        }

        // 3. Удаляем таблицу протоколов
        Schema::dropIfExists('xray_server_protocols');
    }

    private function buildTag(object $server): string
    {
        return match ($server->protocol) {
            'vless' => 'vless-reality',
            'vmess' => 'vmess-ws-tls',
            'trojan' => 'trojan-tcp-tls',
            default => $server->protocol . '-inbound',
        };
    }

    private function buildSettingsFromServer(object $server): array
    {
        $settings = [
            'network' => $server->network ?? 'tcp',
            'security' => $server->security ?? 'reality',
        ];

        // VLESS-специфичное
        if ($server->flow) {
            $settings['flow'] = $server->flow;
        }
        if ($server->fingerprint) {
            $settings['fingerprint'] = $server->fingerprint;
        }

        // Reality
        if ($server->security === 'reality') {
            $settings['dest'] = $server->reality_dest;
            $settings['server_names'] = json_decode($server->reality_server_names ?? '[]', true) ?: [];
            $settings['private_key'] = $server->reality_private_key;
            $settings['public_key'] = $server->reality_public_key;
            $settings['short_ids'] = json_decode($server->reality_short_ids ?? '[]', true) ?: [];
        }

        // WebSocket
        if ($server->network === 'ws' && $server->ws_path) {
            $settings['ws_path'] = $server->ws_path;
        }

        // VMess
        if ($server->protocol === 'vmess') {
            $settings['alter_id'] = $server->alter_id ?? 0;
        }

        return $settings;
    }
};