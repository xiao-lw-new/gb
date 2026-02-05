<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mg_user_contracts', function (Blueprint $table) {
            $table->decimal('mg_bought_amount', 30, 18)->default(0)->after('amount')->comment('购买的MG代币价值($)');
            $table->decimal('granted_quota', 30, 18)->default(0)->after('mg_bought_amount')->comment('由此获得的3倍额度');
        });
    }

    public function down(): void
    {
        Schema::table('mg_user_contracts', function (Blueprint $table) {
            $table->dropColumn(['mg_bought_amount', 'granted_quota']);
        });
    }
};
