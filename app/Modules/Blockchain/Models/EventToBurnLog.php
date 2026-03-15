<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class EventToBurnLog extends Model
{
    protected $table = 'event_to_burn_logs';
    protected $fillable = [
        'chain_id',
        'transaction_hash',
        'log_index',
        'block_number',
        'block_time',
        'contract_address',
        'user_address',
        'user_id',
        'burn_amount_wei',
        'burn_amount',
        'add_burn_quota_wei',
        'add_burn_quota',
        'to_this_amount_wei',
        'to_this_amount',
    ];
}
