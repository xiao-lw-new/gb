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
        Schema::create('blockchain_transaction_queue', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_hash', 100)->index();
            $table->tinyInteger('status')->default(0)->index();
            $table->bigInteger('block_number')->nullable();
            $table->bigInteger('block_time')->nullable();
            $table->json('event_data')->nullable();
            $table->text('message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_transaction_queue');
    }
};
