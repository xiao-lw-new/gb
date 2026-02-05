<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_processor_dispatch_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('status')->default(0)->after('dividend_amount')->comment('0待处理 1已处理 2错误');
            $table->string('remark', 255)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tax_processor_dispatch_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'remark']);
        });
    }
};
