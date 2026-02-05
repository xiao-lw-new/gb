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
        Schema::create('address_sign_code', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('address', 50)->index();
            $table->smallInteger('type')->default(0);
            $table->string('code', 50)->index();
            $table->smallInteger('expired')->default(0)->index();
            $table->smallInteger('retry')->default(0);
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_sign_code');
    }
};
