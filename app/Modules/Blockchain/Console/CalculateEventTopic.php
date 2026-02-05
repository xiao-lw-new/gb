<?php

namespace App\Modules\Blockchain\Console;

use Illuminate\Console\Command;
use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Illuminate\Support\Facades\Log;

class CalculateEventTopic extends Command
{
    protected $signature = 'blockchain:calculate-topic 
                            {event : Event signature (e.g., "Transfer(address,address,uint256)")}
                            {--contract= : Contract address to get ABI from}
                            {--abi-file= : Path to ABI JSON file}
                            {--verify : Verify topic against contract ABI}
                            {--list : List all events from contract ABI}';

    protected $description = 'Calculate event topic hash and verify against contract ABI';

    protected Web3 $web3;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $eventSignature = $this->argument('event');
        $contractAddress = $this->option('contract');
        $abiFile = $this->option('abi-file');
        $verify = $this->option('verify');
        $list = $this->option('list');

        try {
            if ($list || $verify || $contractAddress) {
                // 只有需要网络或ABI验证时才初始化 Web3
            $this->initWeb3();
            }

            if ($list) {
                return $this->listContractEvents($contractAddress, $abiFile);
            }

            if ($verify && ($contractAddress || $abiFile)) {
                return $this->verifyEventTopic($eventSignature, $contractAddress, $abiFile);
            }

            return $this->calculateTopic($eventSignature);

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::channel('blockchain')->error('Calculate topic error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * 初始化 Web3 连接
     */
    protected function initWeb3(): void
    {
        $rpcUrl = config('block_chain.default_rpc.url', 'https://mainnet.infura.io/v3/YOUR_PROJECT_ID');
        $provider = new HttpProvider(new HttpRequestManager($rpcUrl, 30));
        $this->web3 = new Web3($provider);
    }

    /**
     * 计算事件 topic
     */
    protected function calculateTopic(string $eventSignature): int
    {
        $this->info('🔍 Calculating event topic...');
        $this->info("Event signature: {$eventSignature}");

        $topic = $this->getEventTopic($eventSignature);
        
        $this->info('📊 Results:');
        $this->line("Topic: 0x{$topic}");
        $this->line("Event: {$eventSignature}");
        
        // 显示详细信息
        $this->newLine();
        $this->info('📋 Details:');
        $this->line("• Keccak256 hash of: {$eventSignature}");
        $this->line("• Topic length: " . strlen($topic) . " characters");
        $this->line("• Full topic: 0x{$topic}");

        return 0;
    }

    /**
     * 验证事件 topic
     */
    protected function verifyEventTopic(string $eventSignature, ?string $contractAddress, ?string $abiFile): int
    {
        $this->info('🔍 Verifying event topic...');
        $this->info("Event signature: {$eventSignature}");

        // 获取 ABI
        $abi = $this->getContractAbi($contractAddress, $abiFile);
        if (!$abi) {
            $this->error('❌ Failed to get contract ABI');
            return 1;
        }

        // 计算 topic
        $calculatedTopic = $this->getEventTopic($eventSignature);
        
        // 从 ABI 中查找事件
        $eventFromAbi = $this->findEventInAbi($eventSignature, $abi);
        
        if (!$eventFromAbi) {
            $this->warn('⚠️  Event not found in contract ABI');
            $this->info("Calculated topic: 0x{$calculatedTopic}");
            return 0;
        }

        $this->info('✅ Event found in contract ABI');
        $this->newLine();
        $this->info('📊 Verification Results:');
        $this->line("Event name: {$eventFromAbi['name']}");
        $this->line("Calculated topic: 0x{$calculatedTopic}");
        
        // 显示事件参数
        if (!empty($eventFromAbi['inputs'])) {
            $this->newLine();
            $this->info('📋 Event parameters:');
            foreach ($eventFromAbi['inputs'] as $input) {
                $indexed = $input['indexed'] ? ' (indexed)' : '';
                $this->line("• {$input['type']} {$input['name']}{$indexed}");
            }
        }

        return 0;
    }

    /**
     * 列出合约中的所有事件
     */
    protected function listContractEvents(?string $contractAddress, ?string $abiFile): int
    {
        $this->info('📋 Listing contract events...');

        // 获取 ABI
        $abi = $this->getContractAbi($contractAddress, $abiFile);
        if (!$abi) {
            $this->error('❌ Failed to get contract ABI');
            return 1;
        }

        // 查找所有事件
        $events = [];
        foreach ($abi as $item) {
            if ($item['type'] === 'event') {
                $events[] = $item;
            }
        }

        if (empty($events)) {
            $this->warn('⚠️  No events found in contract ABI');
            return 0;
        }

        $this->info("Found " . count($events) . " events:");
        $this->newLine();

        foreach ($events as $event) {
            $this->line("📌 {$event['name']}");
            $this->line("   Topic: 0x" . $this->getEventTopic($this->formatEventSignature($event)));
            
            if (!empty($event['inputs'])) {
                $this->line("   Parameters:");
                foreach ($event['inputs'] as $input) {
                    $indexed = $input['indexed'] ? ' (indexed)' : '';
                    $this->line("     • {$input['type']} {$input['name']}{$indexed}");
                }
            }
            $this->newLine();
        }

        return 0;
    }

    /**
     * 获取合约 ABI
     */
    protected function getContractAbi(?string $contractAddress, ?string $abiFile): ?array
    {
        if ($abiFile) {
            return $this->loadAbiFromFile($abiFile);
        }

        if ($contractAddress) {
            return $this->loadAbiFromContract($contractAddress);
        }

        return null;
    }

    /**
     * 从文件加载 ABI
     */
    protected function loadAbiFromFile(string $abiFile): ?array
    {
        $fullPath = base_path($abiFile);
        
        if (!file_exists($fullPath)) {
            $this->error("ABI file not found: {$fullPath}");
            return null;
        }

        try {
            $abiContent = file_get_contents($fullPath);
            $abi = json_decode($abiContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON in ABI file');
                return null;
            }

            return $abi;
        } catch (\Exception $e) {
            $this->error('Failed to load ABI file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 从合约地址加载 ABI
     */
    protected function loadAbiFromContract(string $contractAddress): ?array
    {
        $this->info("Loading ABI from contract: {$contractAddress}");
        
        // 这里需要实现从区块链获取 ABI 的逻辑
        // 通常需要访问 Etherscan API 或其他服务
        $this->warn('⚠️  Loading ABI from contract address is not implemented yet');
        $this->info('Please use --abi-file option instead');
        
        return null;
    }

    /**
     * 计算事件 topic
     */
    protected function getEventTopic(string $eventSignature): string
    {
        return \kornrunner\Keccak::hash($eventSignature, 256);
    }

    /**
     * 格式化事件签名
     */
    protected function formatEventSignature(array $event): string
    {
        $signature = $event['name'] . '(';
        $inputs = [];
        
        foreach ($event['inputs'] as $input) {
            $inputs[] = $input['type'];
        }
        
        $signature .= implode(',', $inputs) . ')';
        return $signature;
    }

    /**
     * 在 ABI 中查找事件
     */
    protected function findEventInAbi(string $eventSignature, array $abi): ?array
    {
        foreach ($abi as $item) {
            if ($item['type'] === 'event') {
                $formattedSignature = $this->formatEventSignature($item);
                if ($formattedSignature === $eventSignature) {
                    return $item;
                }
            }
        }
        
        return null;
    }


}
