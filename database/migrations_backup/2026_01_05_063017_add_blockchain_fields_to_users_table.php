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
        Schema::table('users', function (Blueprint $table) {
            $table->string('address', 100)->nullable()->unique()->after('id');
            $table->bigInteger('p_id')->default(0)->index()->after('address');
            $table->string('path', 255)->nullable()->index()->after('p_id');
            $table->string('remark', 255)->nullable()->after('path');
            $table->tinyInteger('status')->default(0)->after('remark')->comment('0:预创建, 1:正常, 2:禁用');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address', 'p_id', 'path', 'remark', 'status']);
        });
    }
};
