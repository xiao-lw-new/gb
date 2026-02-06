<?php

namespace App\Modules\Blockchain\Handlers;

use App\Helpers\CommonHelper;
use App\Modules\Blockchain\Helpers\BlockChainHelper;
use App\Modules\Blockchain\Models\TaxProcessorDispatchLog;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\Log;

class FlapTaxProcessorDispatchExecutedHandler implements EventHandler
{
    /**
     * 事件原型: FlapTaxProcessorDispatchExecuted(address indexed taxToken, uint256 feeAmount, uint256 marketAmount, uint256 dividendAmount)
     */
    public function handle(array $event): void
    {
        $data = $event['data'] ?? [];
        $meta = $data['_meta'] ?? [];
        $txHash = strtolower((string) ($meta['transaction_hash'] ?? ''));
        $logIndex = (int) ($meta['log_index'] ?? 0);

        Log::channel('event_tax_processor')->info("[FlapTaxProcessorDispatchExecuted]: Processing, tx: {$txHash}, log_index: {$logIndex}");

        if ($txHash === '') {
            Log::channel('event_tax_processor')->warning('[FlapTaxProcessorDispatchExecuted]: Missing transaction hash, skipping.');
            return;
        }

        if (TaxProcessorDispatchLog::where('transaction_hash', $txHash)->where('log_index', $logIndex)->exists()) {
            Log::channel('event_tax_processor')->info("[FlapTaxProcessorDispatchExecuted]: Transaction {$txHash} log {$logIndex} already processed, skipping.");
            return;
        }

        $taxToken = strtolower((string) ($data['taxToken'] ?? ''));
        $feeWei = (string) ($data['feeAmount'] ?? '0');
        $marketWei = (string) ($data['marketAmount'] ?? '0');
        $dividendWei = (string) ($data['dividendAmount'] ?? '0');

        $blockNumber = (int) ($meta['block_number'] ?? 0);
        $blockTime = $blockNumber > 0 ? BlockChainHelper::blockTime($blockNumber) : null;
        $cutoffTimestamp = (new \DateTimeImmutable('2025-02-06 17:00:00', new \DateTimeZone('Asia/Shanghai')))->getTimestamp();
        if (!$blockTime || $blockTime < $cutoffTimestamp) {
            Log::channel('event_tax_processor')->info("[FlapTaxProcessorDispatchExecuted]: Skip before cutoff, tx: {$txHash}, block_time: " . ($blockTime ?: 'null'));
            return;
        }

        $feeAmount = CommonHelper::fromContractValue($feeWei, 18);
        $marketAmount = CommonHelper::fromContractValue($marketWei, 18);
        $dividendAmount = CommonHelper::fromContractValue($dividendWei, 18);

        TaxProcessorDispatchLog::create([
            'chain_id' => (string) SystemSettingService::getChainId(),
            'transaction_hash' => $txHash,
            'log_index' => $logIndex,
            'block_number' => $blockNumber ?: null,
            'block_time' => $blockTime ? date('Y-m-d H:i:s', $blockTime) : null,
            'contract_address' => strtolower((string) ($meta['contract_address'] ?? '')),
            'tax_token' => $taxToken,
            'fee_amount_wei' => $feeWei,
            'market_amount_wei' => $marketWei,
            'dividend_amount_wei' => $dividendWei,
            'fee_amount' => $feeAmount,
            'market_amount' => $marketAmount,
            'dividend_amount' => $dividendAmount,
            'status' => 0,
        ]);

        Log::channel('event_tax_processor')->info("[FlapTaxProcessorDispatchExecuted]: Logged taxToken={$taxToken}, fee={$feeAmount}, market={$marketAmount}, dividend={$dividendAmount}");
    }
}
