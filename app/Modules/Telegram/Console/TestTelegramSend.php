<?php

namespace App\Modules\Telegram\Console;

use App\Modules\Telegram\Services\TelegramChannelService;
use Illuminate\Console\Command;

class TestTelegramSend extends Command
{
    protected $signature = 'telegram:test-send {channel=rebase} {--title=Test} {--content=Hello} {--now : Send immediately instead of enqueue}';
    protected $description = 'Send a test telegram message to a configured channel (e.g. rebase/stake_info/system_warning/event_report)';

    public function handle(TelegramChannelService $tg): int
    {
        $channel = (string) $this->argument('channel');
        $title = (string) $this->option('title');
        $content = (string) $this->option('content');

        $ok = $this->option('now')
            ? $tg->sendToChannelNow($channel, $title, $content)
            : $tg->sendToChannel($channel, $title, $content);

        $this->info($ok
            ? ($this->option('now') ? "Sent immediately to channel={$channel}" : "Enqueued to channel={$channel}")
            : "Not sent. Check tg_bot log and config tables."
        );

        return self::SUCCESS;
    }
}

