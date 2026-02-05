<?php

namespace App\Modules\Telegram\Console;

use App\Modules\Telegram\Models\Message;
use App\Modules\Telegram\Helpers\BotUtils;
use App\Modules\Telegram\Models\Bot;
use App\Modules\Telegram\Models\BotGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessTelegramMessages extends Command
{
    protected $signature = 'telegram:process-messages';
    protected $description = 'Send pending Telegram messages';

    public function handle()
    {
        $lock = Cache::lock('telegram:process-messages', 55);
        if (! $lock->get()) {
            $this->info('telegram:process-messages is locked, skipping...');
            return;
        }

        try {
            $pending = Message::where('status', 0)
                ->where('retry_count', '<', 5)
                ->orderByDesc('priority')
                ->orderBy('id')
                ->limit(50)
                ->get();

            if ($pending->isEmpty()) {
                return;
            }

            $bot = Bot::where('status', 1)->orderBy('id')->first();
            if (! $bot) {
                Log::channel('tg_bot')->warning('No active telegram bot found; will retry later.');
                return;
            }

            foreach ($pending as $msg) {
                // Mark processing
                $msg->status = 1;
                $msg->save();

                $channel = $msg->sender ?: 'default';
                $group = BotGroup::where('channel', $channel)->first();
                if (! $group || ! $group->chat_id) {
                    $msg->retry_count++;
                    if ($msg->retry_count >= 5) {
                        $msg->status = 3;
                    } else {
                        $msg->status = 0;
                    }
                    $msg->save();
                    Log::channel('tg_bot')->warning("TG group not found for channel={$channel}");
                    continue;
                }

                $text = trim((string) $msg->title) !== ''
                    ? (trim((string) $msg->title) . PHP_EOL . trim((string) $msg->content))
                    : trim((string) $msg->content);

                $ok = BotUtils::sendLongMessage($bot->bot_token, $group->chat_id, $text);
                if ($ok) {
                    $msg->status = 2; // Success
                    $msg->save();
                } else {
                    $msg->retry_count++;
                    if ($msg->retry_count >= 5) {
                        $msg->status = 3; // Failed
                    } else {
                        $msg->status = 0; // Back to pending
                    }
                    $msg->save();
                }
            }
        } finally {
            optional($lock)->release();
        }
    }
}

