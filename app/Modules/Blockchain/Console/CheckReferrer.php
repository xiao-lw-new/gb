<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Models\BlockchainContract;
use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Services\SystemSettingService;
use Illuminate\Console\Command;
use Web3\Web3;
use Web3\Contract;

class CheckReferrer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockchain:check-referrer {address} {uid=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check referrerOf on Community contract using all active RPCs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $address = $this->argument('address');
        $uid = (int) $this->argument('uid');

        $chainId = SystemSettingService::getChainId();
        if (!$chainId) {
            $this->error('CHAIN_ID system setting is not set.');
            return 1;
        }

        // Get Community contract
        $contractModel = BlockchainContract::where('name', 'Community')
            ->where('chain_id', $chainId)
            ->first();

        if (!$contractModel) {
            $this->error("Community contract not found for chain ID {$chainId}");
            return 1;
        }

        $contractAddress = $contractModel->address;
        // Load ABI from file if possible, or use minimal ABI
        $abi = $contractModel->abi;
        if (empty($abi)) {
             // Try to load from build/Community.json
             $abiPath = base_path('build/Community.json');
             if (file_exists($abiPath)) {
                 $abi = json_decode(file_get_contents($abiPath), true);
             } else {
                 $this->error("ABI for Community contract not found.");
                 return 1;
             }
        }

        // Find all active RPC nodes
        $rpcs = BlockchainRpc::where('chain_id', $chainId)
            ->where('status', 1)
            ->get();

        if ($rpcs->isEmpty()) {
            $this->error("No active RPC found for chain_id: {$chainId}");
            return 1;
        }

        $this->info("Checking referrerOf({$uid}, {$address}) on contract {$contractAddress}");
        $this->info("Found " . $rpcs->count() . " active RPCs.");

        foreach ($rpcs as $rpc) {
            $this->line("------------------------------------------------");
            $this->info("RPC Name: {$rpc->name}");
            $this->info("Provider: {$rpc->provider}");

            try {
                $web3 = new Web3($rpc->provider, 10);
                $contract = new Contract($web3->provider, $abi);
                $contract->at($contractAddress);

                $referrer = null;
                // referrerOf(uint256 uid, address account)
                $contract->call('referrerOf', $uid, $address, function ($err, $result) use (&$referrer) {
                    if ($err) {
                        throw new \Exception($err->getMessage());
                    }
                    if (is_array($result)) {
                        $referrer = $result[0] ?? null;
                    } else {
                        $referrer = $result;
                    }
                });

                if ($referrer) {
                    $this->info("Result: {$referrer}");
                } else {
                    $this->warn("Result: null or empty");
                }

            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
            }
        }
        $this->line("------------------------------------------------");

        return 0;
    }
}
