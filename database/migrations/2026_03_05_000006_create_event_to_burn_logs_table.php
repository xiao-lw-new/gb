<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_to_burn_logs', function (Blueprint $table) {
            $table->id();
            $table->string('chain_id')->index();
            $table->string('transaction_hash', 100);
            $table->unsignedInteger('log_index');
            $table->unsignedBigInteger('block_number')->nullable();
            $table->dateTime('block_time')->nullable();
            $table->string('contract_address', 100)->nullable();
            $table->string('user_address', 100)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('burn_amount_wei', 80)->default('0');
            $table->decimal('burn_amount', 36, 18)->default(0);
            $table->string('add_burn_quota_wei', 80)->default('0');
            $table->decimal('add_burn_quota', 36, 18)->default(0);
            $table->string('to_this_amount_wei', 80)->default('0');
            $table->decimal('to_this_amount', 36, 18)->default(0);
            $table->timestamps();

            $table->unique(['transaction_hash', 'log_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_to_burn_logs');
    }
};
