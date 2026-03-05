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
                'name' => 'Token',
                'chain_id' => (string) $chainId,
            ],
            [
                'address' => '0x270e339524b00a5d4c4fe93c29f233e6222bffff',
                'abi_path' => 'build/Token.json',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('blockchain_contract')
            ->where('name', 'Token')
            ->where('address', '0x270e339524b00a5d4c4fe93c29f233e6222bffff')
            ->delete();
    }
};
