<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_price_up_chain', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token_name', 32)->index();
            $table->decimal('token_price', 36, 18);
            $table->string('transaction_hash', 100)->index();
            $table->unsignedTinyInteger('status')->default(0)->index(); // 0=pending, 1=success, 3=failed
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_price_up_chain');
    }
};

