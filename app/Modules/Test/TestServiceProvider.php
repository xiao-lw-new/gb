<?php

namespace App\Modules\Test;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class TestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    protected function registerCommands(): void
    {
        $commandDir = __DIR__ . '/Console/Commands';
        if (!File::isDirectory($commandDir)) return;
        $commands = [];
        foreach (File::allFiles($commandDir) as $file) {
            $class = 'App\\Modules\\Test\\Console\\Commands\\' . $file->getBasename('.php');
            if (class_exists($class)) $commands[] = $class;
        }
        $this->commands($commands);
    }
}
