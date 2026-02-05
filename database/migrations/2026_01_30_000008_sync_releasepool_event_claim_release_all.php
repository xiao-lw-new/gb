<?php

use App\Modules\Mg\Handler\EventClaimReleaseAllHandler;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use kornrunner\Keccak;

return new class extends Migration
{
    public function up(): void
    {
        $abiPath = base_path('build/ReleasePool.json');
        if (!file_exists($abiPath)) {
            return;
        }
        $abi = json_decode(file_get_contents($abiPath), true);
        if (!is_array($abi)) {
            return;
        }

        $chainId = (string) (\App\Services\SystemSettingService::getChainId());
        $address = '0x75eFA4CeAdC7608972eCC810b0587887048F3D72';

        // Ensure ReleasePool contract exists
        $contract = DB::table('blockchain_contract')
            ->where('name', 'ReleasePool')
            ->where('chain_id', $chainId)
            ->first();

        if (! $contract) {
            $id = DB::table('blockchain_contract')->insertGetId([
                'name' => 'ReleasePool',
                'chain_id' => $chainId,
                'address' => strtolower($address),
                'abi_path' => 'build/ReleasePool.json',
                'remark' => 'ReleasePool contract',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $contractId = (int) $id;
        } else {
            $contractId = (int) $contract->id;
        }

        // EventClaimReleaseAll signature/topic
        $signature = $this->buildEventSignature($abi, 'EventClaimReleaseAll');
        if (!$signature) {
            return;
        }
        $topic = '0x' . Keccak::hash($signature, 256);

        DB::table('blockchain_contract_event')->updateOrInsert(
            [
                'contract_id' => $contractId,
                'event_name' => 'EventClaimReleaseAll',
            ],
            [
                'handler' => EventClaimReleaseAllHandler::class,
                'topic' => $topic,
                'remark' => $signature,
                'status' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // Data migration: no rollback.
    }

    private function buildEventSignature(array $abi, string $eventName): ?string
    {
        foreach ($abi as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (($item['type'] ?? null) !== 'event') {
                continue;
            }
            if (($item['name'] ?? null) !== $eventName) {
                continue;
            }
            $inputs = $item['inputs'] ?? [];
            if (!is_array($inputs)) {
                return null;
            }
            $types = array_map(fn ($in) => $this->canonicalType($in), $inputs);
            return $eventName . '(' . implode(',', $types) . ')';
        }

        return null;
    }

    private function canonicalType(array $input): string
    {
        $type = (string) ($input['type'] ?? '');
        if ($type === '') {
            return $type;
        }

        if ($type === 'tuple' || str_starts_with($type, 'tuple')) {
            $suffix = '';
            if (str_ends_with($type, '[]')) {
                $suffix = '[]';
            }
            $components = $input['components'] ?? [];
            if (!is_array($components)) {
                $components = [];
            }
            $inner = implode(',', array_map(fn ($c) => $this->canonicalType($c), $components));
            return '(' . $inner . ')' . $suffix;
        }

        return $type;
    }
};

