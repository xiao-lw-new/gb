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
CREATE OR REPLACE PROCEDURE public.add_columnstore_policy(
    IN hypertable regclass,
    IN after "any" DEFAULT NULL::unknown,
    IN if_not_exists boolean DEFAULT false,
    IN schedule_interval interval DEFAULT NULL::interval,
    IN initial_start timestamp with time zone DEFAULT NULL::timestamp with time zone,
    IN timezone text DEFAULT NULL::text,
    IN created_before interval DEFAULT NULL::interval
)
 LANGUAGE c
AS '$libdir/timescaledb-%s', $$ts_policy_compression_add$$
SQL;
        $sql = sprintf($baseSql, $version);

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS add_columnstore_policy");
    }
};
