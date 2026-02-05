<?php

namespace App\Modules\Blockchain;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class BlockchainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (!env('BLOCKCHAIN_ENABLED', true)) return;
    }

    public function boot(): void
    {
        if (!env('BLOCKCHAIN_ENABLED', true)) return;

        // 确保区块链日志目录存在，避免首次写入失败
        $chainLogDir = storage_path('logs/chain');
        if (!File::isDirectory($chainLogDir)) {
            File::ensureDirectoryExists($chainLogDir);
        }

        if (File::exists(__DIR__ . '/Routes/api.php')) {
            \Illuminate\Support\Facades\Route::middleware(['api', 'restrict_domain:user'])
                ->group(__DIR__ . '/Routes/api.php');
        }

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->app->booted(function () {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
                $schedule->command('transaction:process-queue --max-batch=20')->everyMinute()->withoutOverlapping();
                $schedule->command('blockchain:listen-events')->everyMinute()->withoutOverlapping();
                $schedule->command('blockchain:notify-bonus-pool-send --batch=20')
                    ->everyMinute()
                    ->withoutOverlapping();
                $schedule->command('blockchain:notify-bonus-pool-confirm --confirm-batch=50')
                    ->everyMinute()
                    ->withoutOverlapping();
            });
        }
    }

    protected function registerCommands(): void
    {
        $commandDir = __DIR__ . '/Console';
        if (!File::isDirectory($commandDir)) return;
        $commands = [];
        foreach (File::allFiles($commandDir) as $file) {
            $class = 'App\\Modules\\Blockchain\\Console\\' . $file->getBasename('.php');
            if (class_exists($class)) $commands[] = $class;
        }
        $this->commands($commands);
    }
}

