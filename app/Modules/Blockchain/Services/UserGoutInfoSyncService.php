<?php

namespace App\Modules\Blockchain\Services;

use App\Helpers\CommonHelper;
use App\Models\User;
use App\Modules\Blockchain\Models\BlockchainContract;
use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Modules\Blockchain\Models\EventToBurnLog;
use App\Modules\Blockchain\Models\UserGoutInfo;
use App\Services\SystemSettingService;
use Brick\Math\BigInteger;
use Illuminate\Support\Facades\Log;
use kornrunner\Keccak;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;

class UserGoutInfoSyncService
{
    private const BATCH_SIZE = 300;

    public function sync(): void
    {
        $users = User::whereNotNull('address')
            ->where('address', '!=', '')
            ->select(['id', 'address'])
            ->get();

        if ($users->isEmpty()) {
            Log::channel('event_to_burn')->info('[UserGoutInfoSync]: No users with address found.');
            return;
        }

        $chunks = $users->chunk(self::BATCH_SIZE);

        foreach ($chunks as $chunk) {
            $this->syncBatch($chunk);
        }
    }

    private function syncBatch($users): void
    {
        $addresses = $users->pluck('address')->map(fn ($addr) => strtolower($addr))->values()->toArray();
        $addressToUserId = [];
        foreach ($users as $user) {
            $addressToUserId[strtolower($user->address)] = $user->id;
        }

        try {
            $chainId = SystemSettingService::getChainId();
            $rpc = BlockchainRpc::where('chain_id', $chainId)->where('status', 1)->first();
            if (!$rpc) {
                throw new \RuntimeException("No active RPC found for chain_id: {$chainId}");
            }

            $contract = BlockchainContract::where('name', 'MarketDAO')
                ->where('chain_id', $chainId)
                ->first();
            if (!$contract) {
                throw new \RuntimeException("MarketDAO contract not found for chain_id: {$chainId}");
            }

            $web3 = new Web3(new HttpProvider(new HttpRequestManager($rpc->provider, 30)));
            $callData = $this->encodeGetBatchUsersInfo($addresses);

            $rawResult = null;
            $callError = null;
            $web3->eth->call([
                'to' => $contract->address,
                'data' => $callData,
            ], 'latest', function ($err, $result) use (&$rawResult, &$callError) {
                if ($err !== null) {
                    $callError = $err;
                    return;
                }
                $rawResult = $result;
            });

            if ($callError) {
                throw new \RuntimeException('eth_call failed: ' . $callError->getMessage());
            }

            if (!$rawResult || $rawResult === '0x') {
                Log::channel('event_to_burn')->warning('[UserGoutInfoSync]: Empty result from getBatchUsersInfo.');
                return;
            }

            $userInfoList = $this->decodeRetUserInfoArray($rawResult);

            foreach ($userInfoList as $info) {
                $userAddr = strtolower($info['user']);
                $userId = $addressToUserId[$userAddr] ?? null;
                if (!$userId) {
                    continue;
                }

                $tokenBalance = CommonHelper::fromContractValue($info['tokenBalance'], 18);
                $totalBurnAmount = CommonHelper::fromContractValue($info['burnToken'], 18);

                $goutInfo = UserGoutInfo::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'token_balance' => $tokenBalance,
                        'total_burn_amount' => $totalBurnAmount,
                    ]
                );

                $actualBurnSum = (string) EventToBurnLog::where('user_id', $userId)->sum('burn_amount');
                if (bccomp($goutInfo->burn_amount_new, $actualBurnSum, 18) !== 0) {
                    Log::channel('event_to_burn')->warning("[UserGoutInfoSync]: burn_amount_new mismatch for user_id={$userId}, stored={$goutInfo->burn_amount_new}, actual_sum={$actualBurnSum}, correcting.");
                    $goutInfo->burn_amount_new = $actualBurnSum;
                    $goutInfo->save();
                }
            }

            Log::channel('event_to_burn')->info('[UserGoutInfoSync]: Synced batch, count=' . count($userInfoList));
        } catch (\Throwable $e) {
            Log::channel('event_to_burn')->error('[UserGoutInfoSync]: Batch sync failed: ' . $e->getMessage());
        }
    }

    /**
     * ABI 编码 getBatchUsersInfo(address[]) 调用数据
     */
    private function encodeGetBatchUsersInfo(array $addresses): string
    {
        // function selector: keccak256("getBatchUsersInfo(address[])")
        $selector = substr(Keccak::hash('getBatchUsersInfo(address[])', 256), 0, 8);

        // offset to dynamic array (always 0x20 = 32 for single dynamic param)
        $encoded = str_pad(dechex(32), 64, '0', STR_PAD_LEFT);

        // array length
        $encoded .= str_pad(dechex(count($addresses)), 64, '0', STR_PAD_LEFT);

        // each address, left-padded to 32 bytes
        foreach ($addresses as $addr) {
            $addr = str_replace('0x', '', $addr);
            $encoded .= str_pad($addr, 64, '0', STR_PAD_LEFT);
        }

        return '0x' . $selector . $encoded;
    }

    /**
     * 解码 RetUserInfo[] 返回值
     * 每个 RetUserInfo = (address user, uint256 tokenBalance, uint256 burnToken)
     */
    private function decodeRetUserInfoArray(string $hex): array
    {
        $hex = preg_replace('/^0x/', '', $hex);

        if (strlen($hex) < 128) {
            return [];
        }

        // 第一个 32 字节: offset 指向动态数组
        $arrayOffset = (int) hexdec(substr($hex, 0, 64));
        $arrayStart = $arrayOffset * 2;

        // 数组长度
        $arrayLength = (int) hexdec(substr($hex, $arrayStart, 64));
        if ($arrayLength === 0) {
            return [];
        }

        // 跳过 array length 后是每个 tuple 的 offset 列表
        $offsetsStart = $arrayStart + 64;

        $result = [];
        for ($i = 0; $i < $arrayLength; $i++) {
            // 每个 tuple 的 offset（相对于数组数据起始位置）
            $tupleOffset = (int) hexdec(substr($hex, $offsetsStart + $i * 64, 64));
            $tupleStart = $arrayStart + 64 + $tupleOffset * 2;

            // address (32 bytes, right-aligned)
            $userHex = substr($hex, $tupleStart, 64);
            $user = '0x' . substr($userHex, 24);

            // uint256 tokenBalance
            $tokenBalanceHex = ltrim(substr($hex, $tupleStart + 64, 64), '0') ?: '0';
            $tokenBalance = BigInteger::fromBase($tokenBalanceHex, 16)->__toString();

            // uint256 burnToken
            $burnTokenHex = ltrim(substr($hex, $tupleStart + 128, 64), '0') ?: '0';
            $burnToken = BigInteger::fromBase($burnTokenHex, 16)->__toString();

            $result[] = [
                'user' => strtolower($user),
                'tokenBalance' => $tokenBalance,
                'burnToken' => $burnToken,
            ];
        }

        return $result;
    }

}
