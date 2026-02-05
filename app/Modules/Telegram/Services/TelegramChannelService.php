<?php

namespace App\Modules\Telegram\Services;

use App\Modules\Telegram\Helpers\BotUtils;
use App\Modules\Telegram\Models\Bot;
use App\Modules\Telegram\Models\BotGroup;
use App\Modules\Telegram\Models\Message;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramChannelService
{
    /**
     * Push a message into telegram_messages queue (status=0 Pending).
     * This is safe to call in business logic; the scheduled command will deliver it.
     */
    public function pushToChannelQueue(
        string $channel,
        string $title,
        string $content,
        string $sender = 'system',
        string $senderName = 'system',
        int $priority = 0,
        ?int $businessId = null
    ): void {
        // Deduplicate within last 30 minutes (same title+content+channel).
        $since = now()->subMinutes(30);
        $exists = Message::where('sender', $channel)
            ->where('title', $title)
            ->where('content', $content)
            ->where('created_at', '>=', $since)
            ->exists();

        if ($exists) {
            Log::channel('tg_bot')->info("TG dedup hit (channel={$channel}), skipping enqueue.");
            return;
        }

        Message::create([
            'message_type' => 4,
            'title' => $title,
            'content' => $content,
            'sender' => $channel, // treat sender as channel key (e.g. rebase)
            'sender_name' => $senderName,
            'priority' => $priority,
            'business_id' => $businessId,
            'status' => 0,
            'retry_count' => 0,
        ]);
    }

    /**
     * Enqueue message to a channel.
     *
     * Rationale: Telegram delivery is not 100% reliable; we prefer "store then retry"
     * via `telegram:process-messages` to avoid message loss.
     */
    public function sendToChannel(string $channel, string $title, string $content, int $priority = 0): bool
    {
        $this->pushToChannelQueue($channel, $title, $content, priority: $priority);

        // Best-effort: trigger a single processing run after enqueue (throttled).
        // This helps near-real-time delivery without relying solely on scheduler.
        try {
            if (! app()->runningUnitTests()) {
                // Global throttle: at most once per 10 seconds
                if (Cache::add('telegram:trigger_process:cooldown', 1, now()->addSeconds(10))) {
                    Artisan::call('telegram:process-messages');
                }
            }
        } catch (\Throwable $e) {
            Log::channel('tg_bot')->warning('[TelegramChannelService] trigger process failed: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Send immediately (best-effort) to a channel.
     * Use ONLY for manual testing or urgent ops.
     */
    public function sendToChannelNow(string $channel, string $title, string $content): bool
    {
        $lock = Cache::lock("tg_send_channel_now:{$channel}", 30);
        if (! $lock->get()) {
            Log::channel('tg_bot')->info("TG send-now locked (channel={$channel}), skipping.");
            return false;
        }

        try {
            $bot = Bot::where('status', 1)->orderBy('id')->first();
            $group = BotGroup::where('channel', $channel)->first();

            if (! $bot || ! $group || ! $group->chat_id) {
                Log::channel('tg_bot')->warning("TG config missing for channel={$channel}");
                return false;
            }

            $text = trim($title) . PHP_EOL . trim($content);
            return BotUtils::sendLongMessage($bot->bot_token, $group->chat_id, $text);
        } finally {
            optional($lock)->release();
        }
    }
}

