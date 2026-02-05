<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshActiveCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mg:refresh-active-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh active direct and team counts for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting refresh of user active counts (fast mode)...');

        // Fast path: use closure table `mg_user_relations` + users.active to aggregate counts in SQL.
        // This avoids 4+ queries per user (which is too slow for 10w+ users).
        DB::statement("
            WITH stats AS (
                SELECT
                    r.ancestor_id AS user_id,
                    SUM(CASE WHEN r.distance = 1 THEN 1 ELSE 0 END) AS direct_count,
                    COUNT(*) AS team_count,
                    SUM(CASE WHEN r.distance = 1 AND u.active = 1 THEN 1 ELSE 0 END) AS direct_active_count,
                    SUM(CASE WHEN u.active = 1 THEN 1 ELSE 0 END) AS team_active_count
                FROM mg_user_relations r
                JOIN users u ON u.id = r.user_id
                WHERE r.distance >= 1
                GROUP BY r.ancestor_id
            ),
            upd AS (
                UPDATE mg_vip_performance p
                SET
                    direct_count = s.direct_count,
                    team_count = s.team_count,
                    direct_active_count = s.direct_active_count,
                    team_active_count = s.team_active_count,
                    updated_at = NOW()
                FROM stats s
                WHERE p.user_id = s.user_id
                RETURNING s.user_id
            )
            INSERT INTO mg_vip_performance (
                user_id,
                personal_performance,
                total_performance,
                large_area_id,
                large_area_performance,
                small_area_performance,
                direct_count,
                team_count,
                direct_active_count,
                team_active_count,
                created_at,
                updated_at
            )
            SELECT
                s.user_id,
                0,
                0,
                NULL,
                0,
                0,
                s.direct_count,
                s.team_count,
                s.direct_active_count,
                s.team_active_count,
                NOW(),
                NOW()
            FROM stats s
            LEFT JOIN upd u ON u.user_id = s.user_id
            WHERE u.user_id IS NULL
        ");

        // Optional: users with no descendants are not present in stats; leaving their counts as-is is fine.
        // If you want strict zeros for everyone, we can add a follow-up UPDATE to zero-out missing users.

        $this->info('Active counts refresh completed.');
    }
}
