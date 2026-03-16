<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->tinyInteger('is_qualified')->default(0)->after('new_gout_amount')->comment('基金会达标分红是否达标');
        });
    }

    public function down(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->dropColumn('is_qualified');
        });
    }
};
