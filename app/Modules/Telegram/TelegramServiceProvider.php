<?php

namespace App\Modules\Telegram;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (!env('TELEGRAM_ENABLED', true)) return;
    }

    public function boot(): void
    {
        if (!env('TELEGRAM_ENABLED', true)) return;

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->app->booted(function () {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
                $schedule->command('telegram:process-messages')->everyMinute();
            });
        }
    }

    protected function registerCommands(): void
    {
        $commandDir = __DIR__ . '/Console';
        if (!File::isDirectory($commandDir)) return;
        $commands = [];
        foreach (File::allFiles($commandDir) as $file) {
            $class = 'App\\Modules\\Telegram\\Console\\' . $file->getBasename('.php');
            if (class_exists($class)) $commands[] = $class;
        }
        $this->commands($commands);
    }
}

