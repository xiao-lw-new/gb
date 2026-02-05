<?php

namespace App\Modules\Blockchain\Console;

use App\Services\SystemSettingService;
use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Modules\Blockchain\Models\BlockchainContract;
use App\Modules\Blockchain\Models\BlockchainContractEvent;
use App\Modules\Blockchain\Services\EventLogProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class BlockchainHistoryScanner extends Command
{
    protected $signature = 'blockchain:scan-history
        {--from= : 起始块高度}
        {--to= : 结束块高度}
        {--contract= : 合约名，多个用逗号分隔}
        {--event= : 事件名，多个用逗号分隔}';

    protected $description = 'Scan historical blockchain events between specified blocks';

    protected int $maxBlocksPerRequest = 200;

    public function handle(EventLogProcessor $logProcessor)
    {
        $fromBlock = $this->option('from');
        $toBlock = $this->option('to');
        $contractOption = $this->option('contract');
        $eventOption = $this->option('event');

        if ($fromBlock === null || $toBlock === null) {
            $this->error('必须指定 --from 和 --to');
            return 1;
        }

        $fromBlock = (int) $fromBlock;
        $toBlock = (int) $toBlock;
        if ($fromBlock <= 0 || $toBlock <= 0 || $fromBlock > $toBlock) {
            $this->error('无效的区块范围，请确保 from <= to 且为正整数');
            return 1;
        }

        $contractNames = $this->parseCsvOption($contractOption);
        $eventNames = $this->parseCsvOption($eventOption);

        if (empty($contractNames) || empty($eventNames)) {
            $this->error('必须指定 --contract 与 --event');
            return 1;
        }

        $chainId = SystemSettingService::getChainId();
        $rpc = BlockchainRpc::where('chain_id', $chainId)->where('status', 1)->first();
        if (! $rpc) {
            $this->error("No active RPC found for chain_id: {$chainId}");
            return 1;
        }

        $web3 = new Web3(new HttpProvider(new HttpRequestManager($rpc->provider, 30)));

        $contracts = BlockchainContract::where('chain_id', $rpc->chain_id)
            ->where('status', 1)
            ->whereIn('name', $contractNames)
            ->get();

        if ($contracts->isEmpty()) {
            $this->error('未找到匹配的合约');
            return 1;
        }

        $events = BlockchainContractEvent::whereIn('contract_id', $contracts->pluck('id'))
            ->where('status', 1)
            ->whereIn('event_name', $eventNames)
            ->get();

        if ($events->isEmpty()) {
            $this->error('未找到匹配的事件');
            return 1;
        }

        $addresses = $contracts->pluck('address')->map(fn ($a) => strtolower($a))->toArray();
        $topics = $events->pluck('topic')->unique()->toArray();

        $this->info("History scan: {$fromBlock} -> {$toBlock}");
        $this->info('Contracts: ' . implode(', ', $contractNames));
        $this->info('Events: ' . implode(', ', $eventNames));

        $current = $fromBlock;
        while ($current <= $toBlock) {
            $chunkTo = min($current + $this->maxBlocksPerRequest - 1, $toBlock);
            $this->info("Scanning {$current} -> {$chunkTo}");
            Log::channel('blockchain')->info("History scan: {$current} -> {$chunkTo}");

            $filter = [
                'fromBlock' => '0x' . dechex($current),
                'toBlock' => '0x' . dechex($chunkTo),
                'address' => $addresses,
                'topics' => [$topics],
            ];

            $logs = null;
            $isError = false;
            $web3->eth->getLogs($filter, function ($err, $result) use (&$logs, &$isError) {
                if (!$err) {
                    $logs = $result;
                } else {
                    $logs = [];
                    $isError = true;
                    Log::channel('blockchain')->error("History getLogs error: " . $err->getMessage());
                }
            });

            $wait = 0;
            while ($logs === null && $wait < 20) {
                usleep(500000);
                $wait++;
            }

            if ($logs === null) {
                $this->error('getLogs timeout, aborting.');
                Log::channel('blockchain')->error('History scan getLogs timeout.');
                return 1;
            }

            if ($isError) {
                $this->error('getLogs RPC error, aborting.');
                return 1;
            }

            if (count($logs) > 0) {
                $this->info('Found ' . count($logs) . ' logs.');
                foreach ($logs as $log) {
                    $res = $logProcessor->processLog($log);
                    $status = $res['status'] ?? 'unknown';
                    if ($status === 'success') {
                        $this->line("✅ {$log->transactionHash}");
                    } else {
                        $msg = $res['message'] ?? '';
                        $topic = $res['topic'] ?? '';
                        $this->warn("⚠️ {$log->transactionHash} status={$status} {$msg} {$topic}");
                    }
                }
            }

            $current = $chunkTo + 1;
            usleep(200000);
        }

        $this->info('History scan completed.');
        return 0;
    }

    protected function parseCsvOption(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $value));
        return array_values(array_filter($parts, fn ($v) => $v !== ''));
    }
}

