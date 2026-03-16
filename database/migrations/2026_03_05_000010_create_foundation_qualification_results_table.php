<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foundation_qualification_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('threshold', 36, 18)->default(0)->comment('阈值 = total_burn_amount * 0.1');

            $table->decimal('cond1_value', 36, 18)->default(0)->comment('条件1: 个人 token_balance');
            $table->tinyInteger('cond1_met')->default(0)->comment('条件1 是否达标');

            $table->decimal('cond2_value', 36, 18)->default(0)->comment('条件2: 伞下合计 token_balance');
            $table->tinyInteger('cond2_met')->default(0)->comment('条件2 是否达标');

            $table->decimal('cond3_value', 36, 18)->default(0)->comment('条件3: 伞下合计 burn_amount_new');
            $table->tinyInteger('cond3_met')->default(0)->comment('条件3 是否达标');

            $table->decimal('cond4_value', 36, 18)->default(0)->comment('条件4: 个人 new_gout_amount');
            $table->tinyInteger('cond4_met')->default(0)->comment('条件4 是否达标');

            $table->decimal('cond5_value', 36, 18)->default(0)->comment('条件5: 伞下合计 new_gout_amount');
            $table->tinyInteger('cond5_met')->default(0)->comment('条件5 是否达标');

            $table->tinyInteger('is_qualified')->default(0)->comment('最终是否达标');
            $table->timestamp('checked_at')->nullable()->comment('最后检查时间');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foundation_qualification_results');
    }
};
