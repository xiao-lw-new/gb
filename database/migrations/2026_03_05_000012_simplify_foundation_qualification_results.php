<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foundation_qualification_results', function (Blueprint $table) {
            $table->dropColumn([
                'cond4_value', 'cond4_met',
                'cond5_value', 'cond5_met',
            ]);
        });

        Schema::table('foundation_qualification_results', function (Blueprint $table) {
            $table->renameColumn('cond1_value', 'cond1_value');
            $table->renameColumn('cond2_value', 'cond2_value');
            $table->renameColumn('cond3_value', 'cond3_value');
        });

        // 重置所有数据，下次定时任务会重新计算
        \DB::table('foundation_qualification_results')->truncate();
    }

    public function down(): void
    {
        Schema::table('foundation_qualification_results', function (Blueprint $table) {
            $table->decimal('cond4_value', 36, 18)->default(0)->after('cond3_met');
            $table->tinyInteger('cond4_met')->default(0)->after('cond4_value');
            $table->decimal('cond5_value', 36, 18)->default(0)->after('cond4_met');
            $table->tinyInteger('cond5_met')->default(0)->after('cond5_value');
        });
    }
};
