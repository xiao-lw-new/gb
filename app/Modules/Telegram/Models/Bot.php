<?php

namespace App\Modules\Telegram\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Bot extends Model
{
    protected $fillable = ['bot_name', 'bot_token', 'remark', 'status'];

    public function getTable()
    {
        // Compatibility: some environments may still use plural table names.
        if (Schema::hasTable('telegram_bot')) {
            return 'telegram_bot';
        }
        if (Schema::hasTable('telegram_bots')) {
            return 'telegram_bots';
        }

        return parent::getTable();
    }
}

