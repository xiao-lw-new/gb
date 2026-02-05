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
        Schema::create('blockchain_contract_event', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 100);
            $table->string('handler', 255);
            $table->string('topic', 255)->nullable();
            $table->integer('contract_id');
            $table->string('remark', 255)->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_contract_event');
    }
};
