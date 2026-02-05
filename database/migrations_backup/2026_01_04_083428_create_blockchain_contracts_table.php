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
        Schema::create('blockchain_contract', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->integer('chain_id');
            $table->string('address', 100);
            $table->string('abi_path', 255);
            $table->string('remark', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('default_account', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_contract');
    }
};
