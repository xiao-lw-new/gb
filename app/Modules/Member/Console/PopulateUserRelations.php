<?php

namespace App\Modules\Member\Console;

use App\Modules\Member\Models\UserRelation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateUserRelations extends Command
{
    protected $signature = 'member:populate-user-relations
                            {--truncate : Truncate user_relations before rebuilding}
                            {--chunk=50000 : User rows per batch (used for both path/pid modes)}
                            {--source=pid : Data source: pid|path (default: pid)}
                            {--replace : For each chunk, delete existing relations for user_id range then rebuild in one DB transaction}
                            {--swap : Build into a temporary table and atomically swap}
                            {--no-cycle-check : Skip cycle detection for faster build (assumes p_id graph has no cycles)}';

    protected $description = 'Populate the user_relations table from users path/p_id';

    public function handle(): void
    {
        $chunkSize = max(1000, (int) $this->option('chunk'));
        $source = strtolower((string) $this->option('source'));
        if (!in_array($source, ['path', 'pid'], true)) {
            $this->error("Invalid --source={$source}. Use path|pid.");
            return;
        }
        $noCycleCheck = (bool) $this->option('no-cycle-check');
        $replace = (bool) $this->option('replace');
        $swap = (bool) $this->option('swap');

        if ($swap && ($replace || $this->option('truncate'))) {
            $this->error('--swap cannot be used with --replace or --truncate');
            return;
        }

        $this->info("开始构建 user_relations（source={$source}, chunk={$chunkSize}）...");

        if ($this->option('truncate')) {
            $this->warn('Truncating user_relations...');
            UserRelation::truncate();
        } elseif ($replace) {
            $this->warn('Using --replace mode (chunk-level rebuild in transaction, no global truncate).');
        } elseif ($swap) {
            $this->warn('Using --swap mode (build temp table then atomically swap).');
        }

        $minId = (int) (DB::table('users')->min('id') ?? 0);
        $maxId = (int) (DB::table('users')->max('id') ?? 0);
        if ($maxId <= 0) {
            $this->warn('users 表为空，跳过。');
            return;
        }

        $targetTable = $swap ? 'user_relations_build' : 'user_relations';

        if ($swap) {
            $this->normalizeSwapIndexNames();
            $this->prepareBuildTable();
        }

        $this->info("users.id 范围: {$minId} ~ {$maxId}");
        $bar = $this->output->createProgressBar((int) ceil(($maxId - $minId + 1) / $chunkSize));
        $bar->start();

        for ($start = $minId; $start <= $maxId; $start += $chunkSize) {
            $end = min($maxId, $start + $chunkSize - 1);

            if ($source === 'path') {
                DB::transaction(function () use ($start, $end, $replace, $targetTable) {
                    if ($replace) {
                        DB::statement('DELETE FROM user_relations WHERE user_id BETWEEN ? AND ?', [$start, $end]);
                    }

                    DB::statement(
                        "
                        INSERT INTO {$targetTable} (user_id, ancestor_id, distance)
                        SELECT
                            u.id AS user_id,
                            (p.ancestor_id)::bigint AS ancestor_id,
                            (p.cnt - p.ord) AS distance
                        FROM users u
                        CROSS JOIN LATERAL (
                            SELECT
                                part AS ancestor_id,
                                ordinality AS ord,
                                COUNT(*) OVER () AS cnt
                            FROM regexp_split_to_table(trim(both '|' from COALESCE(u.path, '')), '\\|') WITH ORDINALITY AS t(part, ordinality)
                        ) p
                        WHERE u.id BETWEEN ? AND ?
                          AND COALESCE(u.path, '') <> ''
                        ON CONFLICT (user_id, ancestor_id) DO NOTHING
                        ",
                        [$start, $end]
                    );

                    DB::statement(
                        "
                        INSERT INTO {$targetTable} (user_id, ancestor_id, distance)
                        SELECT id, id, 0
                        FROM users
                        WHERE id BETWEEN ? AND ?
                        ON CONFLICT (user_id, ancestor_id) DO NOTHING
                        ",
                        [$start, $end]
                    );
                });
            } else {
                DB::transaction(function () use ($start, $end, $replace, $noCycleCheck, $targetTable) {
                    if ($replace) {
                        DB::statement('DELETE FROM user_relations WHERE user_id BETWEEN ? AND ?', [$start, $end]);
                    }

                    if ($noCycleCheck) {
                        DB::statement(
                            "
                            WITH RECURSIVE rel AS (
                                SELECT
                                    u.id AS user_id,
                                    u.id AS ancestor_id,
                                    0 AS distance,
                                    u.p_id AS next_pid
                                FROM users u
                                WHERE u.id BETWEEN ? AND ?

                                UNION ALL

                                SELECT
                                    r.user_id,
                                    p.id AS ancestor_id,
                                    r.distance + 1 AS distance,
                                    p.p_id AS next_pid
                                FROM rel r
                                JOIN users p ON p.id = r.next_pid
                                WHERE r.next_pid > 0
                                  AND r.distance < 1000
                            )
                            INSERT INTO {$targetTable} (user_id, ancestor_id, distance)
                            SELECT user_id, ancestor_id, distance FROM rel
                            ON CONFLICT (user_id, ancestor_id) DO NOTHING
                            ",
                            [$start, $end]
                        );
                    } else {
                        DB::statement(
                            "
                            WITH RECURSIVE rel AS (
                                SELECT
                                    u.id AS user_id,
                                    u.id AS ancestor_id,
                                    0 AS distance,
                                    u.p_id AS next_pid,
                                    ARRAY[u.id] AS seen
                                FROM users u
                                WHERE u.id BETWEEN ? AND ?

                                UNION ALL

                                SELECT
                                    r.user_id,
                                    p.id AS ancestor_id,
                                    r.distance + 1 AS distance,
                                    p.p_id AS next_pid,
                                    r.seen || p.id
                                FROM rel r
                                JOIN users p ON p.id = r.next_pid
                                WHERE r.next_pid > 0
                                  AND NOT (p.id = ANY(r.seen))
                                  AND r.distance < 1000
                            )
                            INSERT INTO {$targetTable} (user_id, ancestor_id, distance)
                            SELECT user_id, ancestor_id, distance FROM rel
                            ON CONFLICT (user_id, ancestor_id) DO NOTHING
                            ",
                            [$start, $end]
                        );
                    }
                });
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($swap) {
            $this->warn('Swapping user_relations_build -> user_relations ...');
            $this->swapBuildTable();
        }

        $this->info('✅ user_relations 构建完成。');
    }

    private function normalizeSwapIndexNames(): void
    {
        DB::statement(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_build_ancestor_id_index')
       AND NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_ancestor_id_index') THEN
        EXECUTE 'ALTER INDEX user_relations_build_ancestor_id_index RENAME TO user_relations_ancestor_id_index';
    END IF;
    IF EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_build_user_id_index')
       AND NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_user_id_index') THEN
        EXECUTE 'ALTER INDEX user_relations_build_user_id_index RENAME TO user_relations_user_id_index';
    END IF;
    IF EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_build_user_id_ancestor_id_unique')
       AND NOT EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_user_id_ancestor_id_unique') THEN
        EXECUTE 'ALTER INDEX user_relations_build_user_id_ancestor_id_unique RENAME TO user_relations_user_id_ancestor_id_unique';
    END IF;
    IF EXISTS (
        SELECT 1
        FROM pg_constraint c
        JOIN pg_class t ON t.oid = c.conrelid
        WHERE c.contype = 'p'
          AND t.relname = 'user_relations'
          AND c.conname = 'user_relations_build_pkey'
    ) AND NOT EXISTS (
        SELECT 1
        FROM pg_constraint c
        JOIN pg_class t ON t.oid = c.conrelid
        WHERE c.contype = 'p'
          AND t.relname = 'user_relations'
          AND c.conname = 'user_relations_pkey'
    ) THEN
        EXECUTE 'ALTER TABLE user_relations RENAME CONSTRAINT user_relations_build_pkey TO user_relations_pkey';
    END IF;
END $$;
SQL);
    }

    private function prepareBuildTable(): void
    {
        DB::statement('DROP TABLE IF EXISTS user_relations_build CASCADE');
        DB::statement('
            CREATE TABLE user_relations_build (
                id BIGSERIAL PRIMARY KEY,
                user_id BIGINT NOT NULL,
                ancestor_id BIGINT NOT NULL,
                distance INTEGER NOT NULL
            )
        ');
        DB::statement('CREATE UNIQUE INDEX user_relations_build_user_id_ancestor_id_unique ON user_relations_build (user_id, ancestor_id)');
        DB::statement('CREATE INDEX user_relations_build_user_id_index ON user_relations_build (user_id)');
        DB::statement('CREATE INDEX user_relations_build_ancestor_id_index ON user_relations_build (ancestor_id)');
    }

    private function swapBuildTable(): void
    {
        DB::transaction(function () {
            DB::statement('DROP TABLE IF EXISTS user_relations_old');
            DB::statement('ALTER TABLE user_relations RENAME TO user_relations_old');
            DB::statement('ALTER TABLE user_relations_build RENAME TO user_relations');

            DB::statement(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM pg_constraint c
        JOIN pg_class t ON t.oid = c.conrelid
        WHERE t.relname = 'user_relations' AND c.contype = 'p' AND c.conname = 'user_relations_build_pkey'
    ) THEN
        EXECUTE 'ALTER TABLE user_relations RENAME CONSTRAINT user_relations_build_pkey TO user_relations_pkey';
    END IF;

    IF EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_build_ancestor_id_index') THEN
        EXECUTE 'ALTER INDEX user_relations_build_ancestor_id_index RENAME TO user_relations_ancestor_id_index';
    END IF;
    IF EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_build_user_id_index') THEN
        EXECUTE 'ALTER INDEX user_relations_build_user_id_index RENAME TO user_relations_user_id_index';
    END IF;
    IF EXISTS (SELECT 1 FROM pg_class WHERE relkind = 'i' AND relname = 'user_relations_build_user_id_ancestor_id_unique') THEN
        EXECUTE 'ALTER INDEX user_relations_build_user_id_ancestor_id_unique RENAME TO user_relations_user_id_ancestor_id_unique';
    END IF;
END $$;
SQL);
        });

        DB::statement('DROP TABLE IF EXISTS user_relations_old');
    }
}
