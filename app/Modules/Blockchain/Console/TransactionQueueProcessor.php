<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Services\TransactionProcessingService;
use App\Modules\Blockchain\Services\DaemonManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Modules\Blockchain\Models\BlockchainTransactionQueue;
use App\Modules\Blockchain\Helpers\BlockChainHelper;

class TransactionQueueProcessor extends Command
{
    protected $signature = 'transaction:process-queue 
                            {--daemon : Run in daemon mode}
                            {--interval=3 : Check interval in seconds}
                            {--max-batch=10 : Maximum transactions to process per batch}';
    protected $description = 'Process pending transactions from database queue';
    
    protected $transactionService;
    protected $isRunning = true;

    public function __construct(TransactionProcessingService $transactionService)
    {
        parent::__construct();
        $this->transactionService = $transactionService;
    }

    public function handle()
    {
        $daemon = $this->option('daemon');
        $interval = (int) $this->option('interval');
        $maxBatch = (int) $this->option('max-batch');

        if ($daemon) {
            $this->setupSignalHandlers();
            while ($this->isRunning) {
                try {
                    $this->processBatch($maxBatch);
                    sleep($interval);
                } catch (\Exception $e) {
                    Log::channel('transaction_queue')->error('Daemon error: ' . $e->getMessage());
                    sleep(10);
                }
            }
        } else {
            $this->processBatch($maxBatch);
        }
    }

    protected function setupSignalHandlers(): void
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, fn() => $this->isRunning = false);
            pcntl_signal(SIGINT, fn() => $this->isRunning = false);
        }
    }

    private function processBatch(int $maxBatch)
    {
        $pending = BlockchainTransactionQueue::whereIn('status', [0, 4])
            ->where('retry_count', '<', 5)
            ->orderBy('id', 'asc')
            ->limit($maxBatch)
            ->get();

        if ($pending->isEmpty()) return;

        foreach ($pending as $item) {
            try {
                $item->markProcessing('Processing...');
                $result = $this->transactionService->processTransaction($item->transaction_hash);

                if ($result['status'] === 1) {
                    $item->block_number = is_string($result['block_number']) ? hexdec($result['block_number']) : $result['block_number'];
                    $item->block_time = BlockChainHelper::blockTime($item->block_number);
                    $item->event_data = $result['processed_events'] ?? [];
                    $item->markSuccess('Processed');
                } elseif ($result['status'] === 2) {
                    $item->markDuplicate('Duplicate');
                } elseif ($result['status'] === 3) {
                    $item->markNoTopic('No Match');
                } else {
                    $item->markFailed($result['message'] ?? 'Failed');
                }
            } catch (\Exception $e) {
                $item->markFailed($e->getMessage());
            }
        }
    }
}

