<?php

namespace App\Modules\Blockchain\Helpers;

use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\Cache;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class BlockChainHelper
{
    public static function blockTime($blockNum)
    {
        $chainId = SystemSettingService::getChainId();
        $rpc = BlockchainRpc::where('chain_id', $chainId)->where('status', 1)->inRandomOrder()->first();
        if (! $rpc) {
            throw new \RuntimeException("No active RPC found for chain_id: {$chainId}");
        }

        $provider = new HttpProvider(new HttpRequestManager($rpc->provider, 30));
        $web3 = new Web3($provider);
        $blockTime = null;
        $blockNum = '0x' . dechex((int)$blockNum);
        $web3->eth->getBlockByNumber($blockNum, false, function ($err, $block) use (&$blockTime) {
            if ($err !== null) {
                throw new \Exception("获取区块失败: " . $err->getMessage());
            }

            if (!$block) {
                throw new \Exception("区块不存在");
            }

            // 获取区块时间戳 (Unix时间格式)
            $timestamp = hexdec('0x'.$block->timestamp);

            $blockTime = $timestamp;
        });
        return $blockTime;
    }

    public static function getExplorerUrl(): string
    {
        return Cache::remember('blockchain_explorer_url', 3600, function () {
            $rpc = BlockchainRpc::where('status', 1)->first();
            return $rpc ? $rpc->explorer_url : 'https://bscscan.com';
        });
    }
}

