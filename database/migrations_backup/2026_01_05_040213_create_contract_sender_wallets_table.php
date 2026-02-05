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
        Schema::create('contract_sender_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_name', 100)->index();
            $table->string('address', 100);
            $table->text('encrypted_private_key');
            $table->tinyInteger('status')->default(1);
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_sender_wallets');
    }
};
