<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vpn_clients', function (Blueprint $table) {
            $table->foreignId('xray_server_id')->nullable()->after('user_id')
                ->constrained('xray_servers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vpn_clients', function (Blueprint $table) {
            $table->dropForeign(['xray_server_id']);
            $table->dropColumn('xray_server_id');
        });
    }
};