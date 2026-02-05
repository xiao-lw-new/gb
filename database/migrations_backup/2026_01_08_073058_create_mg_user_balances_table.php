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
        Schema::create('mg_user_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->comment('用户ID');
            
            // 累计产生奖励 (未提现 + 已提现)
            $table->decimal('total', 30, 18)->default(0)->comment('总奖励累计');
            $table->decimal('static', 30, 18)->default(0)->comment('累计模拟静态收益');
            $table->decimal('direct', 30, 18)->default(0)->comment('累计推荐收益');
            $table->decimal('community', 30, 18)->default(0)->comment('累计团队(VIP)收益');

            // 已提现/已结算上链金额
            $table->decimal('claim_total', 30, 18)->default(0)->comment('已提取/上链总额');
            $table->decimal('claim_static', 30, 18)->default(0)->comment('已提取静态收益');
            $table->decimal('claim_direct', 30, 18)->default(0)->comment('已提取推荐收益');
            $table->decimal('claim_community', 30, 18)->default(0)->comment('已提取团队收益');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mg_user_balances');
    }
};
