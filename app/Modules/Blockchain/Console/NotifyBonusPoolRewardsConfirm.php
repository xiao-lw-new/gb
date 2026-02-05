<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Services\BonusPoolNotifyService;
use Illuminate\Console\Command;

class NotifyBonusPoolRewardsConfirm extends Command
{
    protected $signature = 'blockchain:notify-bonus-pool-confirm
                            {--confirm-batch=50 : Maximum records to confirm per run}';

    protected $description = 'Confirm notifyReward transactions and update status';

    public function handle(BonusPoolNotifyService $service): int
    {
        $confirmBatch = (int) $this->option('confirm-batch');
        $service->confirmPending($confirmBatch);

        return self::SUCCESS;
    }
}
