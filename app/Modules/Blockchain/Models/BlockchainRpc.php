<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainRpc extends Model
{
    protected $table = 'blockchain_rpc';
    protected $fillable = ['name', 'provider', 'chain_id', 'gas_limit', 'gas_price', 'status', 'response_time', 'explorer_url'];
}

