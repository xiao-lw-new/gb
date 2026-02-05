<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. 删除冗余的 mg_user_stats 表
        Schema::dropIfExists('mg_user_stats');

        // 2. 移除 mg_user_contracts 表中的额度字段
        Schema::table('mg_user_contracts', function (Blueprint $table) {
            $table->dropColumn(['mg_bought_amount', 'granted_quota']);
        });
    }

    public function down(): void
    {
        // 回退逻辑 (可选)
    }
};
