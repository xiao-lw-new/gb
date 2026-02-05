<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Services\BonusPoolNotifyService;
use Illuminate\Console\Command;

class NotifyBonusPoolRewardsSend extends Command
{
    protected $signature = 'blockchain:notify-bonus-pool-send
                            {--batch=20 : Maximum records to send per run}';

    protected $description = 'Submit notifyReward for pending tax processor logs';

    public function handle(BonusPoolNotifyService $service): int
    {
        $sendBatch = (int) $this->option('batch');
        $service->sendPending($sendBatch);

        return self::SUCCESS;
    }
}
