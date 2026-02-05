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
        Schema::create('blockchain_rpc', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('provider', 100);
            $table->string('chain_id', 25);
            $table->string('gas_limit', 100)->nullable();
            $table->string('gas_price', 100)->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->integer('response_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_rpc');
    }
};
