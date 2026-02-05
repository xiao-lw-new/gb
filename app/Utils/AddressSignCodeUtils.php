<?php

namespace App\Utils;

use App\Models\AddressSignCode;
use App\Models\System\SystemSetting;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\Log;

require_once __DIR__ . '/ecrecover_helper.php';

class AddressSignCodeUtils
{
    /**
     * 获取或生成登录随机码
     */
    public static function getSignCode($address, $type = 0, $uid = 1)
    {
        $address = strtolower($address);
        if (empty($address)) {
            throw new \InvalidArgumentException('Invalid Address');
        }
        
        $address_sign_code = AddressSignCode::where('address', $address)
            ->where('type', $type)
            ->where('expired', 0)
            ->where('retry', '<=', 5)
            ->where('expired_at', '>', now())
            ->first();

        if ($address_sign_code) {
            Log::channel('code_sign')->info("$address 存在未过期签名", ['code' => $address_sign_code->code]);
            return [
                'signature' => self::builtSignature([
                    'code' => $address_sign_code->code,
                    'expired_at' => $address_sign_code->expired_at->timestamp
                ], $uid),
                'expired_at' => $address_sign_code->expired_at->timestamp
            ];
        }

        // 把未过期的标记为过期
        AddressSignCode::where('address', $address)
            ->where('type', $type)
            ->where('expired', 0)
            ->update(['expired' => 1]);

        $code = self::makecode();
        $expired_minutes = SystemSettingService::getExpiredMinutes();
        $expired_at = now()->addMinutes($expired_minutes);

        AddressSignCode::create([
            'address' => $address,
            'code' => $code,
            'expired_at' => $expired_at,
            'type' => $type
        ]);

        Log::channel('code_sign')->info("$address 生成新code: $code, expire_at: " . $expired_at->toDateTimeString());

        return [
            'signature' => self::builtSignature([
                'code' => $code,
                'expired_at' => $expired_at->timestamp
            ], $uid),
            'expired_at' => $expired_at->timestamp
        ];
    }

    /**
     * 构建待签名字符串
     */
    public static function builtSignature($sign_array, $uid = 1)
    {
        $appName = config('app.name', 'MG Ecosystem');
        return "Welcome to sign in $appName\n\nNonce: {$sign_array['code']}\n\nExpires at: " . gmdate('Y-m-d\TH:i:s\Z', $sign_array['expired_at']);
    }

    /**
     * 验证签名
     */
    public static function verifySign($address, $signed, $type = 0, $uid = 1)
    {

        Log::channel('user_auth')->info('传入参数:'.$address.'|'.$signed.'|'.$type.'|'.$uid);
        $address = strtolower($address);
        if (empty($address) || empty($signed) || $signed === 'undefined') {
            return false;
        }

        $address_sign_code = AddressSignCode::where('address', $address)
            ->where('type', $type)
            ->where('expired', 0)
            ->where('retry', '<=', 5)
            ->where('expired_at', '>', now())
            ->first();

        if (!$address_sign_code) {
            return false;
        }

        $code = $address_sign_code->code;
        $expired_at = $address_sign_code->expired_at->timestamp;
        $signature = self::builtSignature([
            'code' => $code,
            'expired_at' => $expired_at
        ], $uid);

        $unsign_address = trim(personal_ecRecover($signature, $signed));

        Log::channel('user_auth')->info('verifySign debug', [
            'input_address' => $address,
            'recovered_address' => $unsign_address,
            'signature_text' => $signature,
            'signed_hash' => $signed,
            'match' => strtolower($address) === strtolower($unsign_address)
        ]);

        if (strtolower($address) === strtolower($unsign_address)) {
            // 验证成功后标记为过期，防止重放攻击
            $address_sign_code->update(['expired' => 1]);
            return true;
        } else {
            $address_sign_code->increment('retry');
            return false;
        }
    }

    /**
     * 生成随机码
     */
    private static function makecode()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 18; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        
        $count = AddressSignCode::where('code', $randomString)->where('expired', 0)->count();
        if ($count > 0) {
            return self::makecode();
        } else {
            return $randomString;
        }
    }
}

