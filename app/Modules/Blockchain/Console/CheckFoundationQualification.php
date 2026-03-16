<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Services\FoundationQualificationService;
use Illuminate\Console\Command;

class CheckFoundationQualification extends Command
{
    protected $signature = 'blockchain:check-foundation-qualification';

    protected $description = 'Check foundation dividend qualification for all users (5 conditions)';

    public function handle(FoundationQualificationService $service): int
    {
        $service->check();

        return self::SUCCESS;
    }
}
