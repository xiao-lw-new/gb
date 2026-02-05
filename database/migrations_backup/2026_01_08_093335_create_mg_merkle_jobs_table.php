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
        Schema::create('mg_merkle_jobs', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->default(0)->comment('0:待处理, 1:处理中, 2:已完成, 3:失败');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('ttl')->nullable()->comment('总耗时(秒)');
            $table->text('error_msg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mg_merkle_jobs');
    }
};
