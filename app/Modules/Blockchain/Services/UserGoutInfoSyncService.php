<?php

namespace App\Modules\Blockchain\Services;

use App\Helpers\CommonHelper;
use App\Models\User;
use App\Modules\Blockchain\Models\EventToBurnLog;
use App\Modules\Blockchain\Models\UserGoutInfo;
use Illuminate\Support\Facades\Log;

class UserGoutInfoSyncService
{
    private const BATCH_SIZE = 300;

    public function sync(): void
    {
        $users = User::whereNotNull('address')
            ->where('address', '!=', '')
            ->select(['id', 'address'])
            ->get();

        if ($users->isEmpty()) {
            Log::channel('event_to_burn')->info('[UserGoutInfoSync]: No users with address found.');
            return;
        }

        $chunks = $users->chunk(self::BATCH_SIZE);

        foreach ($chunks as $chunk) {
            $this->syncBatch($chunk);
        }
    }

    private function syncBatch($users): void
    {
        $addresses = $users->pluck('address')->map(fn ($addr) => strtolower($addr))->values()->toArray();
        $addressToUserId = [];
        foreach ($users as $user) {
            $addressToUserId[strtolower($user->address)] = $user->id;
        }

        try {
            $sender = new ContractSendService('MarketDAO');
            $result = $sender->readContract('getBatchUsersInfo', [$addresses]);

            if (!$result || !isset($result['data_'])) {
                Log::channel('event_to_burn')->warning('[UserGoutInfoSync]: Empty result from getBatchUsersInfo.');
                return;
            }

            $userInfoList = $result['data_'];

            foreach ($userInfoList as $info) {
                $userAddr = strtolower((string) ($info['user'] ?? ''));
                $userId = $addressToUserId[$userAddr] ?? null;
                if (!$userId) {
                    continue;
                }

                $tokenBalance = CommonHelper::fromContractValue((string) ($info['tokenBalance'] ?? '0'), 18);
                $totalBurnAmount = CommonHelper::fromContractValue((string) ($info['burnToken'] ?? '0'), 18);

                $goutInfo = UserGoutInfo::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'token_balance' => $tokenBalance,
                        'total_burn_amount' => $totalBurnAmount,
                    ]
                );

                $actualBurnSum = (string) EventToBurnLog::where('user_id', $userId)->sum('burn_amount');
                if (bccomp($goutInfo->burn_amount_new, $actualBurnSum, 18) !== 0) {
                    Log::channel('event_to_burn')->warning("[UserGoutInfoSync]: burn_amount_new mismatch for user_id={$userId}, stored={$goutInfo->burn_amount_new}, actual_sum={$actualBurnSum}, correcting.");
                    $goutInfo->burn_amount_new = $actualBurnSum;
                    $goutInfo->save();
                }
            }

            Log::channel('event_to_burn')->info('[UserGoutInfoSync]: Synced batch, count=' . count($userInfoList));
        } catch (\Throwable $e) {
            Log::channel('event_to_burn')->error('[UserGoutInfoSync]: Batch sync failed: ' . $e->getMessage());
        }
    }
}
