<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainBlock extends Model
{
    protected $table = 'blockchain_block';
    protected $fillable = ['chain', 'last_block'];
}
