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

    public function upload(?int $limit = null): void
    {
        $query = UserGoutInfo::where('is_qualified', 0)
            ->where('invalid_uploaded', 0);

        if ($limit) {
            $query->limit($limit);
        }

        $pending = $query->pluck('user_id');

        if ($pending->isEmpty()) {
            Log::channel(self::LOG_CHANNEL)->info('[InvalidUpload]: No pending invalid users to upload.');
            return;
        }

        $userMap = User::whereIn('id', $pending)
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->pluck('address', 'id');

        if ($userMap->isEmpty()) {
            Log::channel(self::LOG_CHANNEL)->info('[InvalidUpload]: No addresses found for pending users.');
            return;
        }

        $chunks = $userMap->chunk(self::BATCH_SIZE);

        foreach ($chunks as $chunk) {
            $this->uploadBatch($chunk);
        }
    }

    private function uploadBatch($addressMap): void
    {
        $addresses = $addressMap->values()->map(fn ($addr) => strtolower($addr))->toArray();
        $userIds = $addressMap->keys()->toArray();

        try {
            $sender = new ContractSendService('MarketDAO', self::WALLET_NAME);
            $txHash = $sender->writeContract('updateUserOldNewInvalid', [$addresses]);

            UserGoutInfo::whereIn('user_id', $userIds)
                ->where('invalid_uploaded', 0)
                ->update(['invalid_uploaded' => 1]);

            Log::channel(self::LOG_CHANNEL)->info('[InvalidUpload]: Batch uploaded.', [
                'count' => count($addresses),
                'tx_hash' => $txHash,
            ]);
        } catch (\Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->error('[InvalidUpload]: Batch upload failed: ' . $e->getMessage());
        }
    }
}
