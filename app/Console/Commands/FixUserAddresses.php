<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Modules\Mg\Models\MgUserRewardFlow;
use Elliptic\EC;
use kornrunner\Keccak;
use Illuminate\Support\Facades\Log;

class FixUserAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-addresses {--limit=100} {--force : Force update even if address looks valid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user addresses to be valid ETH addresses using secp256k1 generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        // Find users who have reward flows
        $userIds = MgUserRewardFlow::distinct()->pluck('user_id');
        
        $this->info("Found " . $userIds->count() . " users with reward flows.");

        $query = User::whereIn('id', $userIds);
        
        if (!$force) {
            // Only fix invalid ones (PostgreSQL regex for non-hex chars)
            $query->whereRaw("NOT (address ~* '^0x[0-9a-f]{40}$')");
        }

        $users = $query->limit($limit)->get();

        if ($users->isEmpty()) {
            $this->info("No invalid addresses found to fix.");
            return;
        }

        $this->info("Fixing " . $users->count() . " users...");
        $ec = new EC('secp256k1');

        foreach ($users as $user) {
            try {
                // Generate new key pair
                $keyPair = $ec->genKeyPair();
                $privateKey = $keyPair->getPrivate()->toString(16);

                // Calculate public key and address
                $publicKey = $keyPair->getPublic()->encode('hex');
                // Ethereum address is last 20 bytes of Keccak-256 hash of public key (minus '04' prefix if present, but encode('hex') usually gives uncompressed 04... wait, encode('hex') gives full public key. 
                // Standard: Keccak hash of the public key (excluding the '04' prefix).
                // encode('hex') returns '04' + X + Y. We need to strip '04'.
                
                // Let's verify format. $keyPair->getPublic()->encode('hex') returns uncompressed key starting with 04 usually.
                // The user provided example: hex2bin(substr($publicKey, 2)) -> stripping first 2 chars (1 byte).
                // This assumes '04' prefix.
                
                $address = '0x' . substr(Keccak::hash(hex2bin(substr($publicKey, 2)), 256), -40);

                $oldAddress = $user->address;
                $user->address = $address;
                $user->save();

                $logData = [
                    'user_id' => $user->id,
                    'old_address' => $oldAddress,
                    'new_address' => $address,
                    'private_key' => $privateKey
                ];
                
                Log::channel('single')->info('Address Fixed', $logData);
                $this->info("Fixed User {$user->id}: {$oldAddress} -> {$address}");

            } catch (\Exception $e) {
                $this->error("Failed to fix user {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Done. Check 'single' log or laravel.log for private keys.");
    }
}
