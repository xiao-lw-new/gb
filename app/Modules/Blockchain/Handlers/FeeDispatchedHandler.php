<?php

namespace App\Modules\Blockchain\Handlers;

use App\Helpers\CommonHelper;
use App\Modules\Blockchain\Helpers\BlockChainHelper;
use App\Modules\Blockchain\Models\FeeDispatchedLog;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\Log;

class FeeDispatchedHandler implements EventHandler
{
    /**
     * 事件原型: FeeDispatched(uint256 amountFounder, uint256 amountHolder, uint256 amountBurn, uint256 amountLiquidity, uint256 quoteFounder, uint256 quoteHolder)
     */
    public function handle(array $event): void
    {
        $data = $event['data'] ?? [];
        $meta = $data['_meta'] ?? [];
        $txHash = strtolower((string) ($meta['transaction_hash'] ?? ''));
        $logIndex = (int) ($meta['log_index'] ?? 0);

        Log::channel('event_fee_dispatched')->info("[FeeDispatched]: Processing, tx: {$txHash}, log_index: {$logIndex}");

        if ($txHash === '') {
            Log::channel('event_fee_dispatched')->warning('[FeeDispatched]: Missing transaction hash, skipping.');
            return;
        }

        if (FeeDispatchedLog::where('transaction_hash', $txHash)->where('log_index', $logIndex)->exists()) {
            Log::channel('event_fee_dispatched')->info("[FeeDispatched]: Transaction {$txHash} log {$logIndex} already processed, skipping.");
            return;
        }

        $amountFounderWei = (string) ($data['amountFounder'] ?? '0');
        $amountHolderWei = (string) ($data['amountHolder'] ?? '0');
        $amountBurnWei = (string) ($data['amountBurn'] ?? '0');
        $amountLiquidityWei = (string) ($data['amountLiquidity'] ?? '0');
        $quoteFounderWei = (string) ($data['quoteFounder'] ?? '0');
        $quoteHolderWei = (string) ($data['quoteHolder'] ?? '0');

        $blockNumber = (int) ($meta['block_number'] ?? 0);
        $blockTime = $blockNumber > 0 ? BlockChainHelper::blockTime($blockNumber) : null;
        $cutoffTimestamp = (new \DateTimeImmutable('2025-02-06 17:00:00', new \DateTimeZone('Asia/Shanghai')))->getTimestamp();
        if (!$blockTime || $blockTime < $cutoffTimestamp) {
            Log::channel('event_fee_dispatched')->info("[FeeDispatched]: Skip before cutoff, tx: {$txHash}, block_time: " . ($blockTime ?: 'null'));
            return;
        }

        $amountFounder = CommonHelper::fromContractValue($amountFounderWei, 18);
        $amountHolder = CommonHelper::fromContractValue($amountHolderWei, 18);
        $amountBurn = CommonHelper::fromContractValue($amountBurnWei, 18);
        $amountLiquidity = CommonHelper::fromContractValue($amountLiquidityWei, 18);
        $quoteFounder = CommonHelper::fromContractValue($quoteFounderWei, 18);
        $quoteHolder = CommonHelper::fromContractValue($quoteHolderWei, 18);

        FeeDispatchedLog::create([
            'chain_id' => (string) SystemSettingService::getChainId(),
            'transaction_hash' => $txHash,
            'log_index' => $logIndex,
            'block_number' => $blockNumber ?: null,
            'block_time' => $blockTime ? date('Y-m-d H:i:s', $blockTime) : null,
            'contract_address' => strtolower((string) ($meta['contract_address'] ?? '')),
            'amount_founder_wei' => $amountFounderWei,
            'amount_holder_wei' => $amountHolderWei,
            'amount_burn_wei' => $amountBurnWei,
            'amount_liquidity_wei' => $amountLiquidityWei,
            'quote_founder_wei' => $quoteFounderWei,
            'quote_holder_wei' => $quoteHolderWei,
            'amount_founder' => $amountFounder,
            'amount_holder' => $amountHolder,
            'amount_burn' => $amountBurn,
            'amount_liquidity' => $amountLiquidity,
            'quote_founder' => $quoteFounder,
            'quote_holder' => $quoteHolder,
            'status' => 0,
        ]);

        Log::channel('event_fee_dispatched')->info("[FeeDispatched]: Logged quoteFounder={$quoteFounder}, quoteHolder={$quoteHolder}, amountFounder={$amountFounder}, amountHolder={$amountHolder}, amountBurn={$amountBurn}, amountLiquidity={$amountLiquidity}");
    }
}
