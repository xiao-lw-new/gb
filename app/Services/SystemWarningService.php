<?php

namespace App\Services;

use App\Modules\Telegram\Services\TelegramChannelService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SystemWarningService
{
    /**
     * Send exception notification to Telegram `system_warning` channel with dedup.
     */
    public function notifyException(Throwable $e, array $context = []): void
    {
        // Avoid noisy loops if Telegram is disabled.
        if (! (bool) env('TELEGRAM_ENABLED', true)) {
            return;
        }

        $fingerprint = sha1(implode('|', [
            get_class($e),
            (string) $e->getMessage(),
            (string) $e->getFile(),
            (string) $e->getLine(),
        ]));

        // Deduplicate for 5 minutes
        $key = "system_warning:exception:{$fingerprint}";
        if (! Cache::add($key, 1, now()->addMinutes(5))) {
            return;
        }

        $title = '🚨 System Warning';

        $lines = [];
        $lines[] = '🧩 Type: ' . get_class($e);
        $lines[] = '💥 Message: ' . ((string) $e->getMessage() ?: '(empty)');
        $lines[] = '📍 Location: ' . $e->getFile() . ':' . $e->getLine();
        $lines[] = '🌍 Env: ' . app()->environment();

        // Request context (HTTP only)
        try {
            if (app()->runningInConsole() === false) {
                $req = request();
                $lines[] = '🌐 URL: ' . $req->method() . ' ' . $req->fullUrl();
                $lines[] = '🧾 IP: ' . ($req->ip() ?? '-');
            }
        } catch (\Throwable) {
            // ignore
        }

        if (! empty($context)) {
            $lines[] = '📦 Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Keep trace short; Telegram has message limits anyway (we also split).
        $trace = $e->getTraceAsString();
        if ($trace) {
            $traceLines = explode("\n", $trace);
            $traceLines = array_slice($traceLines, 0, 20);
            $lines[] = '🧵 Trace (top 20):';
            foreach ($traceLines as $t) {
                $lines[] = $t;
            }
        }

        $content = implode(PHP_EOL, $lines);

        try {
            app(TelegramChannelService::class)->sendToChannel('system_warning', $title, $content);
        } catch (\Throwable $tgEx) {
            // Never throw from warning notifier.
            Log::channel('tg_bot')->warning('[SystemWarningService] failed to notify: ' . $tgEx->getMessage());
        }
    }
}

