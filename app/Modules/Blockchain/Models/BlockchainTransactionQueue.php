<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainTransactionQueue extends Model
{
    protected $table = 'blockchain_transaction_queue';

    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_DUPLICATE = 2;
    const STATUS_NO_TOPIC = 3;
    const STATUS_FAILED = 4;
    const STATUS_PROCESSING = 5;

    protected $fillable = [
        'user_id',
        'address',
        'transaction_hash',
        'status',
        'block_number',
        'block_time',
        'event_data',
        'message',
        'retry_count',
    ];

    protected $casts = [
        'event_data' => 'array',
    ];

    public function markProcessing($message = null)
    {
        $this->status = self::STATUS_PROCESSING;
        $this->message = $message;
        $this->save();
    }

    public function markSuccess($message = null)
    {
        $this->status = self::STATUS_SUCCESS;
        $this->message = $message;
        $this->save();
    }

    public function markDuplicate($message = null)
    {
        $this->status = self::STATUS_DUPLICATE;
        $this->message = $message;
        $this->save();
    }

    public function markNoTopic($message = null)
    {
        $this->status = self::STATUS_NO_TOPIC;
        $this->message = $message;
        $this->save();
    }

    public function markFailed($message = null)
    {
        $this->status = self::STATUS_FAILED;
        $this->message = $message;
        $this->retry_count++;
        $this->save();
    }
}

