<?php

namespace App\Modules\Telegram\Models;

use Illuminate\Database\Eloquent\Model;

class ContributionGroup extends Model
{
    // Table name defined by migration: 2026_01_09_024839_create_telegram_contribution_group_table.php
    protected $table = 'telegram_contribution_group';
    protected $fillable = ['name', 'chat_id', 'channel', 'remark'];
}

