<?php

namespace App\Modules\Blockchain\Console;

use App\Modules\Blockchain\Models\ContractSenderWallets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class OnceMakeWalletAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockchain:make-wallet-address';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new sender wallet address entry';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $wallet_name = $this->ask('Wallet Name?');
        $address = $this->ask('Wallet Address?');
        $privateKey = $this->ask('Private Key?');

        if (empty($wallet_name) || empty($address) || empty($privateKey)) {
            $this->error('All fields are required.');
            return 1;
        }

        // Basic address validation
        if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
            $this->error('Invalid Ethereum address format.');
            return 1;
        }

        try {
            ContractSenderWallets::create([
                'wallet_name' => $wallet_name,
                'address' => $address,
                'encrypted_private_key' => Crypt::encryptString(trim($privateKey)),
                'is_default' => 0, // Default value
                'status' => 1 // Active
            ]);

            $this->info("Wallet '{$wallet_name}' created successfully.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create wallet: " . $e->getMessage());
            return 1;
        }
    }
}
