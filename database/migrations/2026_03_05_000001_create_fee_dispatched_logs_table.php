<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_dispatched_logs', function (Blueprint $table) {
            $table->id();
            $table->string('chain_id')->index();
            $table->string('transaction_hash', 100);
            $table->string('notify_transaction_hash', 100)->nullable();
            $table->unsignedInteger('log_index');
            $table->unsignedBigInteger('block_number')->nullable();
            $table->dateTime('block_time')->nullable();
            $table->string('contract_address', 100)->nullable();
            $table->string('amount_founder_wei', 80)->default('0');
            $table->string('amount_holder_wei', 80)->default('0');
            $table->string('amount_burn_wei', 80)->default('0');
            $table->string('amount_liquidity_wei', 80)->default('0');
            $table->string('quote_founder_wei', 80)->default('0');
            $table->string('quote_holder_wei', 80)->default('0');
            $table->string('amount_founder', 80)->default('0');
            $table->string('amount_holder', 80)->default('0');
            $table->string('amount_burn', 80)->default('0');
            $table->string('amount_liquidity', 80)->default('0');
            $table->string('quote_founder', 80)->default('0');
            $table->string('quote_holder', 80)->default('0');
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=sending, 2=confirmed, 3=failed');
            $table->string('remark')->nullable();
            $table->timestamps();

            $table->unique(['transaction_hash', 'log_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_dispatched_logs');
    }
};
