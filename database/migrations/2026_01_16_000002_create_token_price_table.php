<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token_name', 32)->index();
            $table->decimal('token_price', 36, 18);
            $table->timestamps();

            $table->index(['token_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_price');
    }
};

