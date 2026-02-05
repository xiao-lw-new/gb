<?php

namespace App\Modules\Member;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class MemberServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (!env('MEMBER_ENABLED', true)) return;
    }

    public function boot(): void
    {
        if (!env('MEMBER_ENABLED', true)) return;

        if (File::exists(__DIR__ . '/Routes/api.php')) {
            \Illuminate\Support\Facades\Route::middleware(['api', 'restrict_domain:user'])
                ->group(__DIR__ . '/Routes/api.php');
        }

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    protected function registerCommands(): void
    {
        $commandDir = __DIR__ . '/Console';
        if (!File::isDirectory($commandDir)) return;
        $commands = [];
        foreach (File::allFiles($commandDir) as $file) {
            $class = 'App\\Modules\\Member\\Console\\' . $file->getBasename('.php');
            if (class_exists($class)) $commands[] = $class;
        }
        $this->commands($commands);
    }
}

