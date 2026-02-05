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
        Schema::table('merkle_reward_batches', function (Blueprint $table) {
            $table->jsonb('tree_data')->nullable()->after('metadata')->comment('完整的树结构数据');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merkle_reward_batches', function (Blueprint $table) {
            $table->dropColumn('tree_data');
        });
    }
};
