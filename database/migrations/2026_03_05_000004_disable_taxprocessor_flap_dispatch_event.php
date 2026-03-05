<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('blockchain_contract_event')
            ->where('event_name', 'FlapTaxProcessorDispatchExecuted')
            ->update([
                'status' => 0,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('blockchain_contract_event')
            ->where('event_name', 'FlapTaxProcessorDispatchExecuted')
            ->update([
                'status' => 1,
                'updated_at' => now(),
            ]);
    }
};
