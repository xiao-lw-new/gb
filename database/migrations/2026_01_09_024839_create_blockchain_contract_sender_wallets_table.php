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
        Schema::create('blockchain_contract_sender_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('wallet_name', 100)->index('contract_sender_wallets_wallet_name_index');
            $table->string('address', 100);
            $table->text('encrypted_private_key');
            $table->smallInteger('status')->default(1);
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_contract_sender_wallets');
    }
};
