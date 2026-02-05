<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainEvents extends Model
{
    protected $table = 'blockchain_events';
    protected $fillable = [
        'contract_name', 'event_name', 'transaction_hash', 'block_number', 'block_time', 'log_index', 'event_data',
    ];
    protected $casts = ['event_data' => 'array'];
}

