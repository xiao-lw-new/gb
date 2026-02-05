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
            // 首先清理可能存在的重复数据（虽然在开发初期可能性小）
            // 如果是在生产环境，需要更谨慎的处理
            
            // 增加唯一索引
            $table->unique(['transaction_hash', 'log_index'], 'idx_tx_log_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blockchain_events', function (Blueprint $table) {
            $table->dropUnique('idx_tx_log_unique');
        });
    }
};
