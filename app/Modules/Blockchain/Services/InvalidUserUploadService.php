<?php

namespace App\Modules\Blockchain\Services;

use App\Models\User;
use App\Modules\Blockchain\Models\UserGoutInfo;
use Illuminate\Support\Facades\Log;

class InvalidUserUploadService
{
    private const BATCH_SIZE = 100;
    private const LOG_CHANNEL = 'foundation_qualification';
    private const WALLET_NAME = 'un_qualified';

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
        $totalChunks = $chunks->count();

        foreach ($chunks as $chunk) {
            $batchNum++;
            $this->uploadBatch($chunk, $batchNum);

            if ($batchNum < $totalChunks) {
                $this->output("Waiting 15s for tx confirmation before next batch...");
                sleep(15);
            }
        }

        $this->output("Done. Total batches: {$batchNum}");
    }

    private function uploadBatch($addressMap, int $batchNum): void
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

            UserGoutInfo::whereIn('user_id', $userIds)
                ->where('invalid_uploaded', 0)
                ->update([
                    'invalid_uploaded' => 1,
                    'invalid_upload_tx_hash' => $txHash,
                    'invalid_uploaded_at' => now(),
                ]);

            $this->output("Batch #{$batchNum} success! tx_hash: {$txHash}");

            Log::channel(self::LOG_CHANNEL)->info('[InvalidUpload]: Batch uploaded.', [
                'count' => count($addresses),
                'addresses' => $addresses,
                'tx_hash' => $txHash,
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $this->output("Batch #{$batchNum} FAILED: {$msg}", true);
            Log::channel(self::LOG_CHANNEL)->error('[InvalidUpload]: Batch upload failed.', [
                'addresses' => $addresses,
                'error' => $msg,
            ]);
        }
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
