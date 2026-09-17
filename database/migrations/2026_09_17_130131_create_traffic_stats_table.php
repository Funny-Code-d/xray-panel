<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_stats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vpn_client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('period_start');

            $table->unsignedBigInteger('upload')->default(0);
            $table->unsignedBigInteger('downlink')->default(0);
            $table->unsignedBigInteger('total')->default(0);

            $table->unsignedInteger('connections_count')->default(0);
            $table->unsignedInteger('uptime_seconds')->default(0);

            $table->timestamps();

            $table->unique(['vpn_client_id', 'period_start']);
            $table->index(['user_id', 'period_start']);
            $table->index('period_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_stats');
    }
};