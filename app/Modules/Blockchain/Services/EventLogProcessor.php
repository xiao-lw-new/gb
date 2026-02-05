<?php

namespace App\Modules\Blockchain\Services;

use App\Services\SystemSettingService;
use App\Modules\Blockchain\Models\BlockchainContract;
use App\Modules\Blockchain\Models\BlockchainContractEvent;
use App\Modules\Blockchain\Models\BlockchainEvents;
use App\Modules\Blockchain\Services\Traits\EventDataDecoderTrait;
use App\Modules\Blockchain\Helpers\BlockChainHelper;
use Illuminate\Support\Facades\Log;
use Exception;

class EventLogProcessor
{
    use EventDataDecoderTrait;

    /**
     * 仅解析日志数据，不执行 Handler 或保存数据库
     * 
     * @param object $log
     * @param array $abi
     * @return array|null
     */
    public function decodeLog(object $log, array $abi): ?array
    {
        return $this->decodeEventData($log, $abi);
    }

    /**
     * 处理单条区块链日志
     * 
     * @param object $log 原始日志对象 (包含 address, topics, data, blockNumber, logIndex, transactionHash)
     * @param string|null $txHash 交易哈希 (可选，如果不传则从 log 获取)
     * @param int|null $logIndex 日志索引 (可选，如果不传则从 log 获取)
     * @return array ['status' => 'success'|'duplicate'|'no_match'|'failed', 'message' => '...', 'decoded' => [...]]
     */
    public function processLog(object $log, ?string $txHash = null, ?int $logIndex = null): array
    {
        $txHash = $txHash ?? $log->transactionHash;
        $logIndex = $logIndex ?? (is_string($log->logIndex) ? hexdec($log->logIndex) : $log->logIndex);
        $topic = $log->topics[0] ?? null;

        if (!$topic) {
            return ['status' => 'no_match', 'message' => 'No topic found in log'];
        }

        // 1. 重复检查
        if (BlockchainEvents::where('transaction_hash', $txHash)->where('log_index', $logIndex)->exists()) {
            return ['status' => 'duplicate', 'log_index' => $logIndex];
        }

        // 2. 匹配事件定义
        $event = BlockchainContractEvent::where('topic', $topic)->where('status', 1)->first();
        if (!$event) {
            return ['status' => 'no_match', 'log_index' => $logIndex, 'topic' => $topic];
        }

        // 3. 匹配合约定义
        $chainId = SystemSettingService::getChainId();
        $contract = BlockchainContract::where('id', $event->contract_id)
            ->where('chain_id', $chainId)
            ->first();
            
        if (!$contract) {
            return ['status' => 'no_match', 'message' => "Contract ID {$event->contract_id} not found or chain_id mismatch"];
        }

        try {
            // 4. 解析数据
            $abiPath = base_path($contract->abi_path);
            if (!file_exists($abiPath)) {
                return ['status' => 'failed', 'message' => "ABI file not found: {$contract->abi_path}"];
            }
            
            $abi = json_decode(file_get_contents($abiPath), true);
            $decoded = $this->decodeEventData($log, $abi);
            if (!$decoded) {
                return ['status' => 'failed', 'message' => 'Decode event data failed'];
            }

            // 5. 调用业务处理器 (Handler)
            $handlerClass = $event->handler;
            if ($handlerClass && class_exists($handlerClass)) {
                $blockNumber = is_string($log->blockNumber) ? hexdec($log->blockNumber) : $log->blockNumber;
                
                // 统一数据格式传给 Handler
                $handlerData = array_merge($decoded, [
                    '_meta' => [
                        'block_number' => $blockNumber,
                        'transaction_hash' => $txHash,
                        'log_index' => $logIndex,
                        'contract_address' => $log->address,
                        'event_name' => $event->event_name,
                        'contract_name' => $contract->name
                    ]
                ]);

                try {
                    $handler = app($handlerClass);
                    $handler->handle(['data' => $handlerData]);
                } catch (Exception $e) {
                    Log::channel('blockchain')->error("Handler [{$handlerClass}] failed: " . $e->getMessage());
                    // 这里我们继续往下走，记录事件，但可以记录一个错误标记
                }

                // 6. 记录事件到数据库
                BlockchainEvents::updateOrCreate([
                    'transaction_hash' => $txHash,
                    'log_index' => $logIndex,
                ], [
                    'contract_name' => $contract->name,
                    'event_name' => $event->event_name,
                    'block_number' => $blockNumber,
                    'block_time' => BlockChainHelper::blockTime($blockNumber),
                    'event_data' => $decoded
                ]);

                return ['status' => 'success', 'decoded' => $decoded];
            }

            return ['status' => 'failed', 'message' => "Handler class [{$handlerClass}] not found"];

        } catch (Exception $e) {
            Log::channel('blockchain')->error("Process Log Error: " . $e->getMessage());
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }
}
