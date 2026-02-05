<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainEventError extends Model
{
    protected $table = 'blockchain_event_error';
    protected $fillable = ['transaction_hash', 'log_index', 'event_name', 'event_data', 'error_message', 'status'];
    protected $casts = ['event_data' => 'array'];
}

