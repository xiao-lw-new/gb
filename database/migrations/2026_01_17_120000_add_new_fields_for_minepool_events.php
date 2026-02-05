<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mg_turbine_pool_swap_in_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('mg_turbine_pool_swap_in_logs', 'swap_mcn_amount')) {
                $table->decimal('swap_mcn_amount', 36, 18)->default(0)->comment('swap 过程中买入/兑换的 MCN 数量')->after('mx_amount');
            }
        });

        Schema::table('mg_turbine_pool_swap_out_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('mg_turbine_pool_swap_out_logs', 'to_user_mcn_amount')) {
                $table->decimal('to_user_mcn_amount', 36, 18)->default(0)->comment('实际转给用户的 MCN 数量')->after('buy_mcn_amount');
            }
        });

        Schema::table('mg_user_contract_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('mg_user_contract_logs', 'swap_out_mx_amount')) {
                $table->decimal('swap_out_mx_amount', 36, 18)->default(0)->comment('Stake 事件中的 swapOutmMxAmount')->after('amount_after');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mg_turbine_pool_swap_in_logs', function (Blueprint $table) {
            if (Schema::hasColumn('mg_turbine_pool_swap_in_logs', 'swap_mcn_amount')) {
                $table->dropColumn('swap_mcn_amount');
            }
        });

        Schema::table('mg_turbine_pool_swap_out_logs', function (Blueprint $table) {
            if (Schema::hasColumn('mg_turbine_pool_swap_out_logs', 'to_user_mcn_amount')) {
                $table->dropColumn('to_user_mcn_amount');
            }
        });

        Schema::table('mg_user_contract_logs', function (Blueprint $table) {
            if (Schema::hasColumn('mg_user_contract_logs', 'swap_out_mx_amount')) {
                $table->dropColumn('swap_out_mx_amount');
            }
        });
    }
};

