<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xray_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->unsignedInteger('port')->default(443);

            // API
            $table->string('api_host')->default('127.0.0.1');
            $table->unsignedInteger('api_port')->default(10085);

            // Протокол
            $table->string('protocol')->default('vless');           // vless | vmess
            $table->string('inbound_tag')->default('vless-inbound');

            // Транспорт и безопасность
            $table->string('network')->default('tcp');              // tcp | ws | grpc
            $table->string('security')->default('reality');         // none | tls | reality
            $table->string('flow')->nullable();                     // xtls-rprx-vision (только vless)
            $table->unsignedInteger('alter_id')->default(0);        // только vmess

            // Reality (только для security=reality)
            $table->string('reality_dest')->nullable();
            $table->json('reality_server_names')->nullable();
            $table->string('reality_private_key')->nullable();
            $table->string('reality_public_key')->nullable();
            $table->json('reality_short_ids')->nullable();
            $table->string('fingerprint')->default('chrome');

            // WebSocket (только для network=ws)
            $table->string('ws_path')->nullable();

            // API-аутентификация
            $table->string('api_token', 64)->unique();

            // Статус
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->string('status')->default('unknown');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xray_servers');
    }
};