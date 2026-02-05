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
        // 1. MG 业务配置表
        Schema::create('mg_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('default');
            $table->string('remark')->nullable();
            $table->timestamps();
        });

        // 2. 用户合约表
        Schema::create('mg_user_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->decimal('amount', 30, 18)->comment('币的数量');
            $table->string('transaction_hash', 100)->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0:保留, 1:生效, 2:赎回');
            $table->tinyInteger('type')->default(0)->comment('0:7天, 1:15天');
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        // 3. 静态收益模拟表
        Schema::create('mg_static_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('contract_id')->index();
            $table->decimal('amount', 30, 18);
            $table->date('date_ref')->index();
            $table->timestamps();
        });

        // 4. 推荐收益计算明细表
        Schema::create('mg_referral_rewards_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('source_user_id')->index();
            $table->integer('generation');
            $table->unsignedBigInteger('static_reward_id')->index();
            $table->decimal('source_amount', 30, 18);
            $table->decimal('ratio', 8, 4);
            $table->decimal('amount', 30, 18);
            $table->date('date_ref')->index();
            $table->text('remark')->nullable();
            $table->timestamps();
        });

        // 5. VIP 收益计算明细表
        Schema::create('mg_vip_reward_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('date_ref')->index();
            $table->decimal('team_static_total', 30, 18)->comment('下级当日静态收益总和');
            $table->decimal('pre_amount', 30, 18)->comment('预分配数量');
            $table->decimal('distributed_amount', 30, 18)->comment('已分配给其他VIP的数量');
            $table->decimal('actual_amount', 30, 18)->comment('实际获得数量');
            $table->jsonb('distribution_details')->nullable()->comment('分配详情');
            $table->timestamps();
        });

        // 6. 每日推荐收益汇总表
        Schema::create('mg_user_daily_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->decimal('amount', 30, 18);
            $table->date('date_ref')->index();
            $table->timestamps();
        });

        // 7. 每日 VIP 收益表
        Schema::create('mg_vip_daily_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('date_ref')->index();
            $table->integer('vip_level');
            $table->decimal('amount', 30, 18);
            $table->timestamps();
        });

        // 8. VIP 等级表
        Schema::create('mg_vip_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->integer('vip_level')->default(0);
            $table->decimal('vip_ratio', 8, 4)->default(0);
            $table->tinyInteger('type')->default(0)->comment('0:计算, 1:指定');
            $table->timestamps();
        });

        // 9. VIP 业绩计算过程表
        Schema::create('mg_vip_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('total_performance', 30, 18)->default(0);
            $table->unsignedBigInteger('large_area_id')->nullable();
            $table->decimal('large_area_performance', 30, 18)->default(0);
            $table->decimal('small_area_performance', 30, 18)->default(0);
            $table->jsonb('sub_vip_stats')->nullable()->comment('伞下VIP分布情况');
            $table->timestamps();
        });

        // 10. 用户奖励流水表
        Schema::create('user_reward_flow', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type', 50)->comment('奖励类型: referral, vip');
            $table->decimal('amount', 30, 18);
            $table->boolean('visible')->default(false)->comment('是否对用户可见');
            $table->date('date_ref')->index();
            $table->string('tx_hash', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reward_flow');
        Schema::dropIfExists('mg_vip_performance');
        Schema::dropIfExists('mg_vip_levels');
        Schema::dropIfExists('mg_vip_daily_rewards');
        Schema::dropIfExists('mg_user_daily_rewards');
        Schema::dropIfExists('mg_vip_reward_calculations');
        Schema::dropIfExists('mg_referral_rewards_calculations');
        Schema::dropIfExists('mg_static_rewards');
        Schema::dropIfExists('mg_user_contracts');
        Schema::dropIfExists('mg_settings');
    }
};
