<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vpn_clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Xray conf
            $table->string('uuid')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            
            $table->unsignedBigInteger('traffic_used')->default(0);

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->string('email')->nullable()->unique();

            $table->timestamps();

            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vpn_clients');
    }
};
