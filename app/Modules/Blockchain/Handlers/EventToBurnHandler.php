<?php

namespace App\Modules\Blockchain\Handlers;

use App\Helpers\CommonHelper;
use App\Models\User;
use App\Modules\Blockchain\Helpers\BlockChainHelper;
use App\Modules\Blockchain\Models\EventToBurnLog;
use App\Modules\Blockchain\Models\UserGoutInfo;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventToBurnHandler implements EventHandler
{
    /**
     * 事件原型: EventToBurn(address indexed user, uint256 burnAmount, uint256 addBurnQuota, uint256 toThisAmount)
     */
    public function handle(array $event): void
    {
        $data = $event['data'] ?? [];
        $meta = $data['_meta'] ?? [];
        $txHash = strtolower((string) ($meta['transaction_hash'] ?? ''));
        $logIndex = (int) ($meta['log_index'] ?? 0);

        Log::channel('event_to_burn')->info("[EventToBurn]: Processing, tx: {$txHash}, log_index: {$logIndex}");

        if ($txHash === '') {
            Log::channel('event_to_burn')->warning('[EventToBurn]: Missing transaction hash, skipping.');
            return;
        }

        if (EventToBurnLog::where('transaction_hash', $txHash)->where('log_index', $logIndex)->exists()) {
            Log::channel('event_to_burn')->info("[EventToBurn]: Transaction {$txHash} log {$logIndex} already processed, skipping.");
            return;
        }

        $userAddress = strtolower((string) ($data['user'] ?? ''));
        $burnAmountWei = (string) ($data['burnAmount'] ?? '0');
        $addBurnQuotaWei = (string) ($data['addBurnQuota'] ?? '0');
        $toThisAmountWei = (string) ($data['toThisAmount'] ?? '0');

        $blockNumber = (int) ($meta['block_number'] ?? 0);
        $blockTime = $blockNumber > 0 ? BlockChainHelper::blockTime($blockNumber) : null;

        $burnAmount = CommonHelper::fromContractValue($burnAmountWei, 18);
        $addBurnQuota = CommonHelper::fromContractValue($addBurnQuotaWei, 18);
        $toThisAmount = CommonHelper::fromContractValue($toThisAmountWei, 18);

        $user = $userAddress !== '' ? User::whereRaw('LOWER(address) = ?', [$userAddress])->first() : null;
        $userId = $user?->id;

        DB::transaction(function () use (
            $txHash, $logIndex, $blockNumber, $blockTime, $meta,
            $userAddress, $userId,
            $burnAmountWei, $burnAmount,
            $addBurnQuotaWei, $addBurnQuota,
            $toThisAmountWei, $toThisAmount
        ) {
            EventToBurnLog::create([
                'chain_id' => (string) SystemSettingService::getChainId(),
                'transaction_hash' => $txHash,
                'log_index' => $logIndex,
                'block_number' => $blockNumber ?: null,
                'block_time' => $blockTime ? date('Y-m-d H:i:s', $blockTime) : null,
                'contract_address' => strtolower((string) ($meta['contract_address'] ?? '')),
                'user_address' => $userAddress,
                'user_id' => $userId,
                'burn_amount_wei' => $burnAmountWei,
                'burn_amount' => $burnAmount,
                'add_burn_quota_wei' => $addBurnQuotaWei,
                'add_burn_quota' => $addBurnQuota,
                'to_this_amount_wei' => $toThisAmountWei,
                'to_this_amount' => $toThisAmount,
            ]);

            if ($userId) {
                $goutInfo = UserGoutInfo::firstOrCreate(
                    ['user_id' => $userId],
                    ['burn_amount_new' => '0', 'token_balance' => '0', 'total_burn_amount' => '0']
                );
                $goutInfo->burn_amount_new = bcadd($goutInfo->burn_amount_new, $burnAmount, 18);
                $goutInfo->save();
            }
        });

        Log::channel('event_to_burn')->info("[EventToBurn]: Logged user={$userAddress}, userId={$userId}, burnAmount={$burnAmount}, addBurnQuota={$addBurnQuota}, toThisAmount={$toThisAmount}");
    }
}
