<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mg_user_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('total_quota', 30, 18)->default(0)->comment('累计获得的总收益额度');
            $table->decimal('used_quota', 30, 18)->default(0)->comment('已消耗的收益额度');
            $table->decimal('remaining_quota', 30, 18)->default(0)->comment('剩余可用收益额度');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mg_user_stats');
    }
};
