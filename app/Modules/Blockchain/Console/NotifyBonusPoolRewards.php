<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Services\BonusPoolNotifyService;
use Illuminate\Console\Command;

class NotifyBonusPoolRewards extends Command
{
    protected $signature = 'blockchain:notify-bonus-pool
                            {--batch=20 : Maximum records to send per run}
                            {--confirm-batch=50 : Maximum records to confirm per run}';

    protected $description = 'Notify BurnsBonusPool rewards for pending tax processor logs';

    public function handle(BonusPoolNotifyService $service): int
    {
        $sendBatch = (int) $this->option('batch');
        $confirmBatch = (int) $this->option('confirm-batch');

        $service->sendPending($sendBatch);
        $service->confirmPending($confirmBatch);

        return self::SUCCESS;
    }
}
