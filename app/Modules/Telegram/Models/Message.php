<?php

namespace App\Modules\Telegram\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'telegram_messages';
    protected $fillable = [
        'message_type',
        'title',
        'content',
        'sender',
        'sender_name',
        'priority',
        'business_id',
        'status',
        'address_id',
        'address',
        'retry_count',
    ];
}

