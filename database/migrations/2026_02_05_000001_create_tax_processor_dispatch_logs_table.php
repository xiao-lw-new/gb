<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_processor_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('chain_id')->index();
            $table->string('transaction_hash', 100);
            $table->unsignedInteger('log_index');
            $table->unsignedBigInteger('block_number')->nullable();
            $table->dateTime('block_time')->nullable();
            $table->string('contract_address', 100)->nullable();
            $table->string('tax_token', 100)->nullable();
            $table->string('fee_amount_wei', 80)->default('0');
            $table->string('market_amount_wei', 80)->default('0');
            $table->string('dividend_amount_wei', 80)->default('0');
            $table->string('fee_amount', 80)->default('0');
            $table->string('market_amount', 80)->default('0');
            $table->string('dividend_amount', 80)->default('0');
            $table->timestamps();

            $table->unique(['transaction_hash', 'log_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_processor_dispatch_logs');
    }
};
