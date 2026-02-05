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
        Schema::create('blockchain_event_error', function (Blueprint $table) {
            $table->id();
            $table->string('contract_name', 100);
            $table->string('event_name', 100);
            $table->string('transaction_hash', 100);
            $table->string('log_index', 50);
            $table->json('event_data');
            $table->string('handler_class', 255);
            $table->text('error_message');
            $table->text('error_trace')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0:待处理, 1:已修复, 2:已忽略');
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->index(['transaction_hash', 'log_index'], 'idx_error_event_ref');
            $table->index('status', 'idx_error_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_event_error');
    }
};
