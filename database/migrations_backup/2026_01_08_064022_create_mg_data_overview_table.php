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
        Schema::create('mg_data_overview', function (Blueprint $table) {
            $table->id();
            $table->integer('active_users')->default(0)->comment('活跃用户数');
            $table->decimal('total_staked', 30, 18)->default(0)->comment('总质押数量');
            $table->decimal('turbine_pool', 30, 18)->default(0)->comment('涡轮资金池金额');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mg_data_overview');
    }
};
