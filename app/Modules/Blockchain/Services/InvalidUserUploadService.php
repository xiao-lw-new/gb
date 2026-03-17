<?php

namespace App\Modules\Blockchain\Services;

use App\Models\User;
use App\Modules\Blockchain\Models\BlockchainRpc;
use App\Modules\Blockchain\Models\UserGoutInfo;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\Log;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;

class InvalidUserUploadService
{
    private const BATCH_SIZE = 100;
    private const LOG_CHANNEL = 'foundation_qualification';
    private const WALLET_NAME = 'un_qualified';
    private const TX_CHECK_INTERVAL = 3;
    private const TX_CHECK_MAX_WAIT = 120;

    private ?\Illuminate\Console\Command $command = null;

    public function setCommand(\Illuminate\Console\Command $command): self
    {
        $this->command = $command;
        return $this;
    }

    public function upload(?int $limit = null): void
    {
        $query = UserGoutInfo::where('is_qualified', 0)
            ->where('invalid_uploaded', 0);

        if ($limit) {
            $query->limit($limit);
        }

        $pending = $query->pluck('user_id');

        if ($pending->isEmpty()) {
            $this->output('No pending invalid users to upload.');
            return;
        }

        $userMap = User::whereIn('id', $pending)
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->pluck('address', 'id');

        if ($userMap->isEmpty()) {
            $this->output('No addresses found for pending users.');
            return;
        }

        $this->output("Found {$userMap->count()} unqualified addresses to upload.");

        $chunks = $userMap->chunk(self::BATCH_SIZE);
        $batchNum = 0;

        foreach ($chunks as $chunk) {
            $batchNum++;
            $success = $this->uploadBatch($chunk, $batchNum);

            if (!$success) {
                $this->output("Batch #{$batchNum} failed, stopping.", true);
                break;
            }
        }

        $this->output("Done. Total batches processed: {$batchNum}");
    }

    private function uploadBatch($addressMap, int $batchNum): bool
    {
        $addresses = $addressMap->values()->map(fn ($addr) => strtolower($addr))->toArray();
        $userIds = $addressMap->keys()->toArray();

        $this->output("Batch #{$batchNum}: uploading " . count($addresses) . " addresses...");
        foreach ($addresses as $i => $addr) {
            $this->output("  " . ($i + 1) . ". {$addr}");
        }

        try {
            $sender = new ContractSendService('MarketDAO', self::WALLET_NAME);
            $txHash = $sender->writeContract('updateUserOldNewInvalid', [$addresses]);

            $this->output("Batch #{$batchNum} tx submitted: {$txHash}");
            $this->output("Waiting for tx confirmation...");

            $confirmed = $this->waitForConfirmation($txHash);

            if (!$confirmed) {
                $this->output("Batch #{$batchNum} tx NOT confirmed within " . self::TX_CHECK_MAX_WAIT . "s: {$txHash}", true);
                return false;
            }

            UserGoutInfo::whereIn('user_id', $userIds)
                ->where('invalid_uploaded', 0)
                ->update([
                    'invalid_uploaded' => 1,
                    'invalid_upload_tx_hash' => $txHash,
                    'invalid_uploaded_at' => now(),
                ]);

            $this->output("Batch #{$batchNum} confirmed! tx_hash: {$txHash}");

            Log::channel(self::LOG_CHANNEL)->info('[InvalidUpload]: Batch uploaded and confirmed.', [
                'count' => count($addresses),
                'addresses' => $addresses,
                'tx_hash' => $txHash,
            ]);

            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $this->output("Batch #{$batchNum} FAILED: {$msg}", true);
            Log::channel(self::LOG_CHANNEL)->error('[InvalidUpload]: Batch upload failed.', [
                'addresses' => $addresses,
                'error' => $msg,
            ]);
            return false;
        }
    }

    private function waitForConfirmation(string $txHash): bool
    {
        $chainId = SystemSettingService::getChainId();
        $rpc = BlockchainRpc::where('chain_id', $chainId)->where('status', 1)->first();
        if (!$rpc) {
            return false;
        }

        $web3 = new Web3(new HttpProvider(new HttpRequestManager($rpc->provider, 30)));
        $waited = 0;

        while ($waited < self::TX_CHECK_MAX_WAIT) {
            sleep(self::TX_CHECK_INTERVAL);
            $waited += self::TX_CHECK_INTERVAL;

            $receipt = null;
            $web3->eth->getTransactionReceipt($txHash, function ($err, $result) use (&$receipt) {
                if (!$err && $result) {
                    $receipt = $result;
                }
            });

            if ($receipt) {
                $status = $receipt->status ?? null;
                if ($status === '0x1' || $status === '1') {
                    $this->output("Tx confirmed after {$waited}s, status: success");
                    return true;
                }
                $this->output("Tx confirmed after {$waited}s, status: FAILED (reverted)", true);
                return false;
            }

            $this->output("  ...waiting ({$waited}s)");
        }

        return false;
    }

    private function output(string $message, bool $isError = false): void
    {
        $logMessage = "[InvalidUpload]: {$message}";

        if ($this->command) {
            $isError ? $this->command->error($logMessage) : $this->command->info($logMessage);
        }

        if ($isError) {
            Log::channel(self::LOG_CHANNEL)->error($logMessage);
        } else {
            Log::channel(self::LOG_CHANNEL)->info($logMessage);
        }
    }
}
