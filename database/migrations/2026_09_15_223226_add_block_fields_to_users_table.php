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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('approval_status');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->string('block_reason')->nullable()->after('blocked_at');
            $table->foreignId('blocked_by')->nullable()->after('block_reason')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropColumn(['is_blocked', 'blocked_at', 'block_reason', 'blocked_by']);
        });
    }
};
