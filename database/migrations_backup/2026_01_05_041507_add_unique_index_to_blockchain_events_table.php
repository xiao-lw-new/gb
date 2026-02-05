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
        Schema::table('blockchain_events', function (Blueprint $table) {
            // 添加唯一索引，支持批量 upsert 操作并防止重复
            $table->unique(['transaction_hash', 'log_index', 'event_name'], 'idx_event_unique_hash_log_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blockchain_events', function (Blueprint $table) {
            $table->dropUnique('idx_event_unique_hash_log_name');
        });
    }
};
