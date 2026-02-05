<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_bot', function (Blueprint $table) {
            $table->id();
            $table->string('bot_name', 100);
            $table->string('bot_token', 255);
            $table->string('remark')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('telegram_bot_group', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('chat_id', 100);
            $table->string('channel', 100);
            $table->string('remark')->nullable();
            $table->timestamps();
        });

        Schema::create('telegram_contribution_group', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('chat_id', 100);
            $table->string('channel', 100);
            $table->string('remark')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer('message_type')->default(4)->comment('4: Telegram');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('sender', 100)->nullable();
            $table->string('sender_name', 100)->nullable();
            $table->integer('priority')->default(0);
            $table->integer('business_id')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Processing, 2: Success, 3: Failed');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('address', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('group_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('message_type')->default(4);
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('sender', 100)->nullable();
            $table->string('sender_name', 100)->nullable();
            $table->integer('priority')->default(0);
            $table->integer('business_id')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('address', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_messages');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('telegram_contribution_group');
        Schema::dropIfExists('telegram_bot_group');
        Schema::dropIfExists('telegram_bot');
    }
};
