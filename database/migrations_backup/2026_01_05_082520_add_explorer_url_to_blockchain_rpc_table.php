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
        Schema::table('blockchain_rpc', function (Blueprint $table) {
            $table->string('explorer_url', 255)->nullable()->after('provider')->comment('区块浏览器地址');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blockchain_rpc', function (Blueprint $table) {
            $table->dropColumn('explorer_url');
        });
    }
};
