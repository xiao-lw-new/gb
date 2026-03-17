<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->tinyInteger('invalid_uploaded')->default(0)->after('is_qualified')
                ->comment('不达标地址是否已上传合约: 0=未上传, 1=已上传');
        });
    }

    public function down(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->dropColumn('invalid_uploaded');
        });
    }
};
