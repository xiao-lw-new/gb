<?php

namespace App\Modules\Telegram\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotUtils
{
    public static function sendMessage(string $token, string $chatId, string $content, ?string $parseMode = null): bool
    {
        try {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $payload = [
                'chat_id' => $chatId,
                'text' => $content,
                'disable_web_page_preview' => true,
            ];

            // IMPORTANT:
            // Telegram Markdown parsing is fragile (e.g. unescaped "_" in "block_time" will fail).
            // Default to plain text unless caller explicitly asks for a parse mode.
            if ($parseMode) {
                $payload['parse_mode'] = $parseMode;
            }

            $response = Http::post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::channel('tg_bot')->error("Failed to send TG message: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::channel('tg_bot')->error("TG API Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Telegram has a 4096 char limit per message. This splits long texts.
     */
    public static function sendLongMessage(string $token, string $chatId, string $content, int $chunkSize = 3800, ?string $parseMode = null): bool
    {
        $content = (string) $content;
        $content = trim($content);

        if ($content === '') {
            return true;
        }

        $chunks = [];
        $len = mb_strlen($content, 'UTF-8');
        for ($offset = 0; $offset < $len; $offset += $chunkSize) {
            $chunks[] = mb_substr($content, $offset, $chunkSize, 'UTF-8');
        }

        foreach ($chunks as $chunk) {
            if (! self::sendMessage($token, $chatId, $chunk, $parseMode)) {
                return false;
            }
            usleep(200000); // 200ms
        }

        return true;
    }
}

