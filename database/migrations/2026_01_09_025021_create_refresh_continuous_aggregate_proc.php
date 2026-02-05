<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $row = DB::selectOne("select extversion from pg_extension where extname = 'timescaledb'");
        $version = $row?->extversion ?? null;
        if (!$version) {
            throw new \RuntimeException('timescaledb extension is required but not installed.');
        }

        $baseSql = <<<'SQL'
CREATE OR REPLACE PROCEDURE public.refresh_continuous_aggregate(
    IN continuous_aggregate regclass,
    IN window_start "any",
    IN window_end "any",
    IN force boolean DEFAULT false,
    IN options jsonb DEFAULT NULL::jsonb
)
 LANGUAGE c
AS '$libdir/timescaledb-%s', $$ts_continuous_agg_refresh$$
SQL;
        $sql = sprintf($baseSql, $version);

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS refresh_continuous_aggregate");
    }
};
