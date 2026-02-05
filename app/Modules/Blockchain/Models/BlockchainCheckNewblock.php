<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainCheckNewblock extends Model
{
    protected $table = 'blockchain_check_newblock';
    protected $fillable = ['chain_id', 'last_block_number', 'status'];
}

