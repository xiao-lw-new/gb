<?php

namespace App\Modules\Blockchain\Console;

use App\Services\SystemSettingService;
use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Modules\Blockchain\Models\BlockchainBlock;
use App\Modules\Blockchain\Models\BlockchainContract;
use App\Modules\Blockchain\Models\BlockchainContractEvent;
use App\Modules\Blockchain\Services\EventLogProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Exception;

class BlockchainEventListener extends Command
{
    protected $signature = 'blockchain:listen-events';
    protected $description = 'Scan blockchain for contract events with multi-level delays (scheduled)';

    // 定义扫描延迟级别（块数）：12 (实时级), 62 (稳定级), 112 (最终兜底级)
    protected array $delayLevels = [12, 62, 112];
    protected int $maxBlocksPerRequest = 100; // 降低每次扫描的最大块数，防止 RPC 报错

    public function handle(EventLogProcessor $logProcessor)
    {
        $this->info('Starting blockchain event listener (Multi-Level Confirmation)...');
        Log::channel('blockchain')->info('Starting blockchain event listener (Multi-Level Confirmation)...');

        // 获取系统配置的 chain_id（DB/缓存不可用时回退到 .env CHAIN_ID）
        try {
            $chainId = SystemSettingService::getChainId();
        } catch (\Throwable $e) {
            $chainId = (string) (env('CHAIN_ID', '56'));
            $this->warn("SystemSettingService unavailable, using CHAIN_ID from env: {$chainId}");
            Log::channel('blockchain')->warning("SystemSettingService unavailable, using CHAIN_ID from env: {$chainId}", ['error' => $e->getMessage()]);
        }

        // 根据系统配置的 chain_id 查找活跃的 RPC
        $rpc = BlockchainRpc::where('chain_id', $chainId)->where('status', 1)->first();
        if (!$rpc) {
            $this->error("No active RPC found for chain_id: {$chainId}");
            Log::channel('blockchain')->error("No active RPC found for chain_id: {$chainId}");
            return 1;
        }

        $this->info("Using RPC: {$rpc->provider} (id={$rpc->id})");
        Log::channel('blockchain')->info('Using RPC', [
            'provider' => $rpc->provider,
            'rpc_id' => $rpc->id,
            'chain_id' => $rpc->chain_id,
        ]);

        $web3 = new Web3(new HttpProvider(new HttpRequestManager($rpc->provider, 30)));
        
        $currentBlock = 0;
        $blockError = null;
        $web3->eth->blockNumber(function ($err, $number) use (&$currentBlock, &$blockError) {
            if ($err) {
                $blockError = $err;
                return;
            }
            $currentBlock = intval($number->toString());
        });

        if ($currentBlock === 0) {
            $msg = $blockError ? $blockError->getMessage() : 'unknown error';
            $this->error("Failed to get current block number. {$msg}");
            Log::channel('blockchain')->error('Failed to get current block number.', ['error' => $msg]);
            return 1;
        }

        // 只获取当前 chain_id 下活跃的合约和事件
        $contracts = BlockchainContract::where('chain_id', $rpc->chain_id)
            ->where('status', 1)
            ->get();
        $addresses = $contracts->pluck('address')->map(fn($a) => strtolower($a))->toArray();
        $events = BlockchainContractEvent::whereIn('contract_id', $contracts->pluck('id'))
            ->where('status', 1)
            ->get();
        $topics = $events->pluck('topic')->unique()->toArray();

        if (empty($addresses) || empty($topics)) {
            $this->warn('No contracts or events to watch.');
            Log::channel('blockchain')->warning('No contracts or events to watch.');
            return 0;
        }

        // 依次处理每个延迟级别
        foreach ($this->delayLevels as $delay) {
            try {
                $this->processDelayLevel($logProcessor, $web3, $rpc, $currentBlock, $delay, $addresses, $topics);
                // 增加一个小延迟，防止请求频率过高
                usleep(500000); // 0.5s
            } catch (Exception $e) {
                $this->error("Error processing delay level [{$delay}]: " . $e->getMessage());
                Log::channel('blockchain')->error("Delay level [{$delay}] failed: " . $e->getMessage());
            }
        }

        return 0;
    }

    /**
     * 处理特定延迟级别的扫描
     */
    protected function processDelayLevel(EventLogProcessor $logProcessor, $web3, $rpc, $currentBlock, $delay, $addresses, $topics)
    {
        // 为每一级确认维护独立的扫描进度记录
        $trackerKey = $rpc->chain_id . ':' . $delay;
        $blockTracker = BlockchainBlock::where('chain', $trackerKey)->first();

        if (!$blockTracker) {
            // 首次初始化：从当前块向前推对应延迟的位置开始
            $blockTracker = BlockchainBlock::create([
                'chain' => $trackerKey,
                'last_block' => $currentBlock - $delay - 1
            ]);
            $this->info("Initialized tracker for {$trackerKey} at block {$blockTracker->last_block}");
            Log::channel('blockchain')->info("Initialized tracker for {$trackerKey} at block {$blockTracker->last_block}");
        }

        // 追赶模式：循环直到追上 targetBlock 或者 达到单次运行限制
        $maxLoops = 100; // 每次执行最多处理 100 个批次 (10000 块)
        $loopCount = 0;

        while ($loopCount < $maxLoops) {
            $targetBlock = $currentBlock - $delay;
            $fromBlock = $blockTracker->last_block + 1;

            if ($fromBlock > $targetBlock) {
                if ($loopCount === 0) {
                    $this->info("Level [{$delay}]: Already up to date (Last: {$blockTracker->last_block}, Target: {$targetBlock})");
                }
                break;
            }

            // 限制扫描范围
            $toBlock = min($fromBlock + $this->maxBlocksPerRequest - 1, $targetBlock);
            $this->info("Level [{$delay}]: Scanning {$fromBlock} -> {$toBlock} (Current: {$currentBlock})");
            Log::channel('blockchain')->info("Level [{$delay}]: Scanning {$fromBlock} -> {$toBlock} (Current: {$currentBlock})");

            $filter = [
                'fromBlock' => '0x' . dechex($fromBlock),
                'toBlock' => '0x' . dechex($toBlock),
                'address' => $addresses,
                'topics' => [$topics]
            ];

            $logs = null;
            $isError = false;
            $web3->eth->getLogs($filter, function ($err, $result) use (&$logs, &$isError, $delay) {
                if (!$err) {
                    $logs = $result;
                } else {
                    $logs = [];
                    $isError = true;
                    Log::channel('blockchain')->error("getLogs error (Delay: {$delay}): " . $err->getMessage());
                }
            });

            // 必须等待异步回调完成
            $wait = 0;
            while ($logs === null && $wait < 20) {
                usleep(500000); // 0.5s
                $wait++;
            }

            if ($logs === null) {
                $this->error("Level [{$delay}]: getLogs timeout. Skipping update.");
                Log::channel('blockchain')->error("Level [{$delay}]: getLogs timeout. Skipping update.");
                return;
            }

            if ($isError) {
                $this->error("Level [{$delay}]: getLogs RPC error. Skipping update.");
                return;
            }

            if (count($logs) > 0) {
                $this->info("Level [{$delay}]: Found " . count($logs) . " logs.");
                Log::channel('blockchain')->info("Level [{$delay}]: Found " . count($logs) . " logs.");
                foreach ($logs as $log) {
                    // 调用公共的日志处理器 (包含幂等检查)
                    $res = $logProcessor->processLog($log);
                    
                    if ($res['status'] === 'success') {
                        $this->info("Level [{$delay}]: ✅ Successfully processed TX {$log->transactionHash}");
                        Log::channel('blockchain')->info("Level [{$delay}]: ✅ Successfully processed TX {$log->transactionHash}");
                    }
                }
            } else {
                Log::channel('blockchain')->info("Level [{$delay}]: No logs found in range {$fromBlock} -> {$toBlock}");
            }

            // 只有在无错误且未超时的情况下，才更新扫描高度
            $blockTracker->update(['last_block' => $toBlock]);
            
            // Increment loop counter
            $loopCount++;
            
            // Small sleep to prevent RPC rate limiting
            usleep(500000); // 0.5s
        }
    }
}
