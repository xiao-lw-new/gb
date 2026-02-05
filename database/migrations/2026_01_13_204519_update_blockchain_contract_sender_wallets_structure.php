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
        Schema::table('blockchain_contract_sender_wallets', function (Blueprint $table) {
            $table->string('wallet_name', 100)->nullable()->change();
            $table->string('address', 100)->nullable()->change();
            $table->longText('encrypted_private_key')->nullable()->change();
            $table->tinyInteger('is_default')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blockchain_contract_sender_wallets', function (Blueprint $table) {
            $table->string('wallet_name', 100)->nullable(false)->change();
            $table->string('address', 100)->nullable(false)->change();
            $table->text('encrypted_private_key')->nullable(false)->change();
            $table->dropColumn('is_default');
        });
    }
};
