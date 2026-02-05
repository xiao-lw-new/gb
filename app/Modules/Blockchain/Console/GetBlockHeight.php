<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Services\SystemSettingService;
use Illuminate\Console\Command;
use Web3\Web3;

class GetBlockHeight extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockchain:block-height';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get current block height from all configured RPCs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $chainId = SystemSettingService::getChainId();
        if (!$chainId) {
            $this->error('CHAIN_ID system setting is not set.');
            return 1;
        }

        // Find all active RPC nodes
        $rpcs = BlockchainRpc::where('chain_id', $chainId)
            ->where('status', 1)
            ->get();

        if ($rpcs->isEmpty()) {
            $this->error("No active RPC found for chain_id: {$chainId}");
            return 1;
        }

        $this->info("Found " . $rpcs->count() . " active RPCs for chain ID {$chainId}.");

        foreach ($rpcs as $rpc) {
            $this->line("------------------------------------------------");
            $this->info("RPC Name: {$rpc->name}");
            $this->info("Provider: {$rpc->provider}");

            try {
                $web3 = new Web3($rpc->provider, 10); // Use shorter timeout for check
                
                $blockHeight = null;
                $web3->eth->blockNumber(function ($err, $number) use (&$blockHeight) {
                    if ($err) {
                        throw new \Exception($err->getMessage());
                    }
                    $blockHeight = $number;
                });

                if ($blockHeight) {
                    // Handle BigInteger if returned
                    if (is_object($blockHeight) && method_exists($blockHeight, 'toString')) {
                        $blockHeight = $blockHeight->toString();
                    }
                    
                    $this->info("Current Block Height: <comment>{$blockHeight}</comment>");
                } else {
                     $this->error("Failed to retrieve block height (empty result).");
                }
            } catch (\Exception $e) {
                $this->error("Error connecting to RPC: " . $e->getMessage());
            }
        }
        $this->line("------------------------------------------------");

        return 0;
    }
}
