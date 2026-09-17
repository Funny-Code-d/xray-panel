<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vpn_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('xray_last_upload')->default(0)->after('traffic_used');
            $table->unsignedBigInteger('xray_last_downlink')->default(0)->after('xray_last_upload');
            $table->timestamp('last_synced_at')->nullable()->after('xray_last_downlink');
        });
    }

    public function down(): void
    {
        Schema::table('vpn_clients', function (Blueprint $table) {
            $table->dropColumn(['xray_last_upload', 'xray_last_downlink', 'last_synced_at']);
        });
    }
};