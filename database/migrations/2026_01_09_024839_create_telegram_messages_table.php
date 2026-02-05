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
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('message_type')->default(4)->comment('4: Telegram');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('sender', 100)->nullable();
            $table->string('sender_name', 100)->nullable();
            $table->integer('priority')->default(0);
            $table->integer('business_id')->nullable();
            $table->smallInteger('status')->default(0)->comment('0: Pending, 1: Processing, 2: Success, 3: Failed');
            $table->bigInteger('address_id')->nullable();
            $table->string('address', 100)->nullable();
            $table->timestamps();
            $table->integer('retry_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
