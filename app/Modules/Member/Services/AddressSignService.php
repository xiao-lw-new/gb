<?php

namespace App\Modules\Member\Services;

use App\Utils\AddressSignCodeUtils;
use Illuminate\Support\Facades\Log;

class AddressSignService
{
    /**
     * 获取签名代码
     */
    public function getSignCode($address, $type = 0, $uid = 1)
    {
        try {
            return AddressSignCodeUtils::getSignCode($address, $type, $uid);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::channel('code_sign')->error('Failed to get sign code', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to generate sign code');
        }
    }

    /**
     * 验证签名
     */
    public function verifySign($address, $signed, $type = 0, $uid = 1)
    {
        return AddressSignCodeUtils::verifySign($address, $signed, $type, $uid);
    }
}

