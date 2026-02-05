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
CREATE OR REPLACE PROCEDURE public.merge_chunks(IN chunks regclass[])
 LANGUAGE c
AS '$libdir/timescaledb-%s', $$ts_merge_chunks$$
SQL;
        $sql = sprintf($baseSql, $version);

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS merge_chunks");
    }
};
