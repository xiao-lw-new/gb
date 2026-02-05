<?php

namespace App\Modules\Telegram\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BotGroup extends Model
{
    protected $fillable = ['name', 'chat_id', 'channel', 'remark'];

    public function getTable()
    {
        // Compatibility: some environments may still use plural table names.
        if (Schema::hasTable('telegram_bot_group')) {
            return 'telegram_bot_group';
        }
        if (Schema::hasTable('telegram_bot_groups')) {
            return 'telegram_bot_groups';
        }

        return parent::getTable();
    }
}

