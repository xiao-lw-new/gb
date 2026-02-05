<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainContract extends Model
{
    protected $table = 'blockchain_contract';
    protected $fillable = ['name', 'chain_id', 'address', 'abi_path', 'remark', 'status', 'default_account'];
}

