<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->decimal('new_gout_amount', 36, 18)->default(0)->after('total_burn_amount');
        });
    }

    public function down(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->dropColumn('new_gout_amount');
        });
    }
};
