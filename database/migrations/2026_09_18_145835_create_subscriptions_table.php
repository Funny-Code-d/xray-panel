<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('channel');              // email | telegram
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Одна подписка на (user, tag, channel)
            $table->unique(['user_id', 'tag_id', 'channel']);
            $table->index(['user_id', 'is_active']);
            $table->index(['tag_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};