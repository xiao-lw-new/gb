<?php

namespace App\Modules\Blockchain\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DaemonManager
{
    protected string $daemonName;
    protected string $command;
    protected string $logChannel;

    public function __construct(string $daemonName, string $command, string $logChannel = 'stack')
    {
        $this->daemonName = $daemonName;
        $this->command = $command;
        $this->logChannel = $logChannel;
    }

    public function recordRestart(): void
    {
        $key = "daemon_restart_count:{$this->daemonName}";
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, 86400);
        
        Log::channel($this->logChannel)->warning("Daemon {$this->daemonName} restarted. Total restarts today: " . ($count + 1));
    }

    public function resetRestartCount(): void
    {
        Cache::forget("daemon_restart_count:{$this->daemonName}");
    }

    public function getRestartCount(): int
    {
        return Cache::get("daemon_restart_count:{$this->daemonName}", 0);
    }
}

