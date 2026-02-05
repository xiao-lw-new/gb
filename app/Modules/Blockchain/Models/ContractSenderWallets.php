<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class ContractSenderWallets extends Model
{
    protected $table = 'blockchain_contract_sender_wallets';
    protected $fillable = [
        'wallet_name',
        'address',
        'encrypted_private_key',
        'is_default',
        'status',
        'remark'
    ];
}
