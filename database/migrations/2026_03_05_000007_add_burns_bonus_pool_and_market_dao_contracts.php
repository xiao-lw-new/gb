<?php

use App\Services\SystemSettingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $chainId = SystemSettingService::getChainId();

        DB::table('blockchain_contract')->updateOrInsert(
            [
                'name' => 'BurnsBonusPool',
                'chain_id' => (string) $chainId,
            ],
            [
                'address' => '0x4355911383cE4e928b247cc2dA95DAc8E5505Ea2',
                'abi_path' => 'build/BurnsBonusPool.json',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('blockchain_contract')->updateOrInsert(
            [
                'name' => 'MarketDAO',
                'chain_id' => (string) $chainId,
            ],
            [
                'address' => '0xC46b3B160B997765497565D0f8378cCAF5926366',
                'abi_path' => 'build/MarketDAO.json',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('blockchain_contract')->where('name', 'BurnsBonusPool')->delete();
        DB::table('blockchain_contract')->where('name', 'MarketDAO')->delete();
    }
};
