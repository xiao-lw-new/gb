<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->string('invalid_upload_tx_hash', 100)->nullable()->after('invalid_uploaded')
                ->comment('上传不达标地址的交易hash');
            $table->timestamp('invalid_uploaded_at')->nullable()->after('invalid_upload_tx_hash')
                ->comment('上传时间');
        });
    }

    public function down(): void
    {
        Schema::table('user_gout_info', function (Blueprint $table) {
            $table->dropColumn(['invalid_upload_tx_hash', 'invalid_uploaded_at']);
        });
    }
};
