<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainContractEvent extends Model
{
    protected $table = 'blockchain_contract_event';
    protected $fillable = ['event_name', 'handler', 'topic', 'contract_id', 'remark', 'status'];
}

