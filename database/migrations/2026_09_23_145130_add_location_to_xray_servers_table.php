<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xray_servers', function (Blueprint $table) {
            $table->string('country', 2)->nullable()->after('name');
            $table->string('country_name')->nullable()->after('country');
            $table->string('city')->nullable()->after('country_name');
        });
    }

    public function down(): void
    {
        Schema::table('xray_servers', function (Blueprint $table) {
            $table->dropColumn(['country', 'country_name', 'city']);
        });
    }
};