<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class TaxProcessorDispatchLog extends Model
{
    protected $table = 'tax_processor_dispatch_logs';
    protected $fillable = [
        'chain_id',
        'transaction_hash',
        'notify_transaction_hash',
        'log_index',
        'block_number',
        'block_time',
        'contract_address',
        'tax_token',
        'fee_amount_wei',
        'market_amount_wei',
        'dividend_amount_wei',
        'fee_amount',
        'market_amount',
        'dividend_amount',
        'status',
        'remark',
    ];
}
