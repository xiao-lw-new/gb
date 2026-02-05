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
        Schema::table('messages', function (Blueprint $table) {
            $table->integer('retry_count')->default(0)->after('status');
        });

        Schema::table('group_messages', function (Blueprint $table) {
            $table->integer('retry_count')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('retry_count');
        });

        Schema::table('group_messages', function (Blueprint $table) {
            $table->dropColumn('retry_count');
        });
    }
};
