<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use kornrunner\Keccak;

return new class extends Migration
{
    public function up(): void
    {
        $abiPath = base_path('build/MinePool.json');
        if (!file_exists($abiPath)) {
            return;
        }

        $abi = json_decode(file_get_contents($abiPath), true);
        if (!is_array($abi)) {
            return;
        }

        // Find MinePool contracts (may exist on multiple chains)
        $contracts = DB::table('blockchain_contract')->where('name', 'MinePool')->get();
        if ($contracts->isEmpty()) {
            return;
        }

        $events = array_values(array_filter($abi, fn ($i) => is_array($i) && ($i['type'] ?? null) === 'event'));

        $handlerMap = [
            'EventStake' => \App\Modules\Mg\Handler\EventStakeHandler::class,
            'EventUnstake' => \App\Modules\Mg\Handler\EventUnstakeHandler::class,
            'EventBuyProfitQuota' => \App\Modules\Mg\Handler\EventBuyProfitQuotaHandler::class,
            'EventTurbinePoolSwapIn' => \App\Modules\Mg\Handler\EventTurbinePoolSwapInHandler::class,
            'EventTurbinePoolSwapOut' => \App\Modules\Mg\Handler\EventTurbinePoolSwapOutHandler::class,
            'EventClaim' => \App\Modules\Mg\Services\MgClaimEventHandler::class,
        ];
        $defaultHandler = \App\Modules\Blockchain\Handlers\NoopEventHandler::class;

        foreach ($contracts as $contract) {
            $contractId = (int) $contract->id;

            // Load existing rows for this contract to avoid overwriting custom handlers unnecessarily.
            $existingRows = DB::table('blockchain_contract_event')
                ->where('contract_id', $contractId)
                ->get()
                ->keyBy('event_name');

            foreach ($events as $ev) {
                $eventName = $ev['name'] ?? null;
                $inputs = $ev['inputs'] ?? [];
                if (!$eventName || !is_array($inputs)) {
                    continue;
                }

                $types = array_map(fn ($in) => $in['type'] ?? '', $inputs);
                $types = array_values(array_filter($types, fn ($t) => $t !== ''));
                $signature = $eventName . '(' . implode(',', $types) . ')';
                $topic = '0x' . Keccak::hash($signature, 256);

                $existing = $existingRows->get($eventName);
                $handler = $handlerMap[$eventName] ?? ($existing->handler ?? $defaultHandler);

                DB::table('blockchain_contract_event')->updateOrInsert(
                    [
                        'contract_id' => $contractId,
                        'event_name' => $eventName,
                    ],
                    [
                        'handler' => $handler,
                        'topic' => $topic,
                        'remark' => $signature,
                        'status' => 1,
                        'updated_at' => now(),
                        'created_at' => $existing->created_at ?? now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Data migration: no rollback.
    }
};

