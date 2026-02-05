<?php

namespace App\Services;

use App\Models\System\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    private const CACHE_PREFIX = 'system_setting:';
    private const CACHE_TTL = 3600; // 缓存1小时

    /**
     * 获取系统设置值
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            return SystemSetting::getValue($key, $default);
        });
    }

    /**
     * 设置系统设置值
     */
    public static function set(string $key, $value, array $extra = []): bool
    {
        try {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                array_merge(['value' => (string)$value], $extra)
            );
            
            // 清除缓存
            Cache::forget(self::CACHE_PREFIX . $key);
            
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to set system setting [$key]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取链ID
     */
    public static function getChainId(): string
    {
        return self::get('chain_id', env('CHAIN_ID', '97'));
    }

    /**
     * 获取登录Nonce过期时间（分钟）
     */
    public static function getExpiredMinutes(): int
    {
        return (int) self::get('EXPIRED_MINUTES', 60);
    }

    /**
     * 清除缓存
     */
    public static function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }
}
