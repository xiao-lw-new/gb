<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Services\UserGoutInfoSyncService;
use Illuminate\Console\Command;

class SyncUserGoutInfo extends Command
{
    protected $signature = 'blockchain:sync-user-gout-info';

    protected $description = 'Sync user gout info (token_balance, total_burn_amount) from MarketDAO contract';

    public function handle(UserGoutInfoSyncService $service): int
    {
        $service->sync();

        return self::SUCCESS;
    }
}
