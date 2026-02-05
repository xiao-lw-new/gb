<?php

use Elliptic\EC;
use kornrunner\Keccak;
use Illuminate\Support\Facades\Log;

if (!function_exists('personal_ecRecover')) {
    function personal_ecRecover($message, $signature) {
        $adapter = new EC('secp256k1');
        $signature = substr($signature, 2); // 去掉 0x

        $r = substr($signature, 0, 64);
        $s = substr($signature, 64, 64);
        $v = hexdec(substr($signature, 128, 2));
        $v = intval($v); // 确保是整数

        // 以太坊的 v 值为 27 或 28，有时会加上 2 * chainId + 35
        // 但在 eth_sign 中，通常是 27 或 28 (legacy) 或 0/1 (EIP-155)
        // 这里做一个兼容处理
        if ($v > 30) {
            $v -= 27; 
        }
        if ($v >= 27) {
            $recId = $v - 27;
        } else {
            $recId = $v;
        }

        $msg_length = strlen($message);
        $prefixedMessage = "\x19Ethereum Signed Message:\n{$msg_length}{$message}";
        $hash = Keccak::hash($prefixedMessage, 256);

        Log::info('ECRecover Debug', [
            'message' => $message,
            'prefixed_message_hex' => bin2hex($prefixedMessage),
            'hash' => $hash,
            'v' => $v,
            'recId' => $recId,
            'r' => $r,
            's' => $s
        ]);

        try {
            // elliptic-php recoverPubKey: ($msg, $signature, $recoveryParam, $encoding)
            $signatureObj = ["r" => $r, "s" => $s];
            $pubKey = $adapter->recoverPubKey($hash, $signatureObj, (int)$recId, "hex");
            
            $pubKeyHex = $pubKey->encode("hex");
            $pubKeyHex = substr($pubKeyHex, 2); // 去掉 04 前缀

            $addressRaw = Keccak::hash(hex2bin($pubKeyHex), 256);
            $address = '0x' . substr($addressRaw, 24);

            Log::info('ECRecover Result', ['recovered_address' => $address]);

            return $address;
        } catch (\Exception $e) {
            Log::error('ECRecover Exception', ['msg' => $e->getMessage()]);
            return null;
        }
    }
}
