<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->boolean('is_super')->default(false)->after('modules');
        });

        DB::table('admin_users')
            ->where('username', 'admin')
            ->orWhere('email', 'admin@mcnext.io')
            ->orWhere('id', 1)
            ->update(['is_super' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('is_super');
        });
    }
};
