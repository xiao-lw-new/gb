<?php

namespace App\Modules\Blockchain\Services;

use App\Services\SystemSettingService;
use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Modules\Blockchain\Services\EventLogProcessor;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Exception;

class TransactionProcessingService
{
    protected ?Web3 $web3 = null;
    protected EventLogProcessor $logProcessor;

    public function __construct(EventLogProcessor $logProcessor)
    {
        $this->logProcessor = $logProcessor;
    }

    protected function initWeb3(): void
    {
        $chainId = SystemSettingService::getChainId();
        $rpc = BlockchainRpc::where('chain_id', $chainId)->where('status', 1)->first();
        if (!$rpc) throw new Exception("No active RPC found for chain_id: {$chainId}");
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager($rpc->provider, 30)));
    }

    protected function ensureInitialized(): void
    {
        if (!$this->web3) $this->initWeb3();
    }

    /**
     * 处理完整的交易哈希
     */
    public function processTransaction(string $txHash): array
    {
        try {
            $this->ensureInitialized();
            $receipt = $this->getTransactionReceipt($txHash);
            
            if (!$receipt) {
                return ['success' => false, 'status' => 4, 'message' => 'Receipt not found'];
            }

            // 检查交易状态 (0x1 为成功)
            $isSuccess = ($receipt->status ?? '') === '0x1' || (isset($receipt->status) && hexdec($receipt->status) === 1);
            if (!$isSuccess) {
                return ['success' => false, 'status' => 4, 'message' => 'Transaction failed on chain'];
            }

            $logs = $receipt->logs ?? [];
            $results = [];
            $hasSuccess = false;
            $hasDuplicate = false;
            $hasNoMatch = false;

            foreach ($logs as $index => $log) {
                // 调用公共的日志处理器
                // IMPORTANT: do NOT use the receipt array index as log_index.
                // log_index must be the on-chain logIndex, otherwise the same on-chain event
                // can be inserted twice (once via getLogs, once via receipt processing).
                $res = $this->logProcessor->processLog($log, $txHash);
                
                $results[] = $res;
                if ($res['status'] === 'success') $hasSuccess = true;
                if ($res['status'] === 'duplicate') $hasDuplicate = true;
                if ($res['status'] === 'no_match') $hasNoMatch = true;
            }

            $finalStatus = 4; // 默认失败
            if ($hasSuccess) $finalStatus = 1; // 成功处理
            elseif ($hasDuplicate) $finalStatus = 2; // 已处理过
            elseif ($hasNoMatch || empty($logs)) $finalStatus = 3; // 未匹配到关注事件

            return [
                'success' => $finalStatus <= 2,
                'status' => $finalStatus,
                'block_number' => is_string($receipt->blockNumber) ? hexdec($receipt->blockNumber) : $receipt->blockNumber,
                'processed_events' => $results
            ];
        } catch (Exception $e) {
            return ['success' => false, 'status' => 4, 'message' => $e->getMessage()];
        }
    }

    /**
     * 获取交易回执
     */
    protected function getTransactionReceipt(string $txHash): ?object
    {
        $receipt = null;
        $this->web3->eth->getTransactionReceipt($txHash, function ($err, $result) use (&$receipt) {
            if (!$err) $receipt = $result;
        });
        
        $wait = 0;
        while ($receipt === null && $wait < 20) { // 增加等待时间
            usleep(500000); // 0.5s
            $wait++;
        }
        return $receipt;
    }
}
