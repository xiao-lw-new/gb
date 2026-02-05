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
        Schema::create('blockchain_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_hash')->nullable()->index();
            $table->integer('block_number')->nullable();
            $table->bigInteger('block_time')->nullable()->index();
            $table->string('event_name')->nullable()->index();
            $table->json('event_data')->nullable();
            $table->string('contract_name')->nullable();
            $table->string('log_index')->nullable();
            $table->timestamps();

            $table->unique(['transaction_hash', 'log_index', 'event_name'], 'idx_event_unique_hash_log_name');
            $table->unique(['transaction_hash', 'log_index'], 'idx_tx_log_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_events');
    }
};
