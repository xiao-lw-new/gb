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
            $table->bigIncrements('id');
            $table->string('event_name', 100);
            $table->string('handler');
            $table->string('topic')->nullable();
            $table->integer('contract_id');
            $table->string('remark')->nullable();
            $table->smallInteger('status')->nullable()->default(1);
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
