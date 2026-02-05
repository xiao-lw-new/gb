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
        // 1. Blockchain 插件
        if (Schema::hasTable('contract_sender_wallets')) {
            Schema::rename('contract_sender_wallets', 'blockchain_contract_sender_wallets');
        }

        // 2. Mg 插件
        if (Schema::hasTable('user_reward_flow')) {
            Schema::rename('user_reward_flow', 'mg_user_reward_flow');
        }

        // 3. Telegram 插件
        if (Schema::hasTable('messages')) {
            Schema::rename('messages', 'telegram_messages');
        }
        if (Schema::hasTable('group_messages')) {
            Schema::rename('group_messages', 'telegram_group_messages');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('blockchain_contract_sender_wallets')) {
            Schema::rename('blockchain_contract_sender_wallets', 'contract_sender_wallets');
        }
        if (Schema::hasTable('mg_user_reward_flow')) {
            Schema::rename('mg_user_reward_flow', 'user_reward_flow');
        }
        if (Schema::hasTable('telegram_messages')) {
            Schema::rename('telegram_messages', 'messages');
        }
        if (Schema::hasTable('telegram_group_messages')) {
            Schema::rename('telegram_group_messages', 'group_messages');
        }
    }
};
