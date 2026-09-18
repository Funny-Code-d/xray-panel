<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();        // updates, incidents, maintenance
            $table->string('name');                  // Обновления, Инциденты
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#3b82f6'); // hex-цвет для бейджа
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};