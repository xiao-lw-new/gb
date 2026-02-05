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
            $table->id();
            $table->string('transaction_hash', 255)->nullable()->index();
            $table->integer('block_number')->nullable();
            $table->unsignedBigInteger('block_time')->nullable()->index();
            $table->string('event_name', 255)->nullable()->index();
            $table->json('event_data')->nullable();
            $table->string('contract_name', 255)->nullable();
            $table->string('log_index', 255)->nullable();
            $table->timestamps();
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
