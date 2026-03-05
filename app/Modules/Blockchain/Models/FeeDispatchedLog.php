<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class FeeDispatchedLog extends Model
{
    protected $table = 'fee_dispatched_logs';
    protected $fillable = [
        'chain_id',
        'transaction_hash',
        'notify_transaction_hash',
        'log_index',
        'block_number',
        'block_time',
        'contract_address',
        'amount_founder_wei',
        'amount_holder_wei',
        'amount_burn_wei',
        'amount_liquidity_wei',
        'quote_founder_wei',
        'quote_holder_wei',
        'amount_founder',
        'amount_holder',
        'amount_burn',
        'amount_liquidity',
        'quote_founder',
        'quote_holder',
        'status',
        'remark',
    ];
}
