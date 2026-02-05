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
        Schema::create('user_team_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->comment('用户ID');
            $table->jsonb('subordinate_ids')->nullable()->comment('所有下级IDs');
            $table->unsignedBigInteger('large_area_id')->nullable()->comment('大区ID (业绩/人数最大的下级分支)');
            $table->jsonb('small_area_ids')->nullable()->comment('小区IDs (其他下级分支)');
            $table->jsonb('direct_referral_ids')->nullable()->comment('直推IDs');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_team_info');
    }
};
