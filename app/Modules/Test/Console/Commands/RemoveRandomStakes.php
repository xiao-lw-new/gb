<?php

namespace App\Modules\Test\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveRandomStakes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:remove-random-stakes {--count=100 : Number of users to remove stakes for}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Randomly remove all stakes for a specific number of users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');

        $this->info("Picking {$count} random users with active stakes...");

        // 1. 随机选出有质押的 100 个用户 ID
        // 在 PostgreSQL 中，DISTINCT 和 ORDER BY RANDOM() 配合时，ORDER BY 表达式必须出现在 SELECT 列表中
        // 这里改用 GROUP BY 来实现同样的效果，并保持兼容性
        $userIds = DB::table('mg_user_contracts')
            ->select('user_id')
            ->groupBy('user_id')
            ->inRandomOrder()
            ->limit($count)
            ->pluck('user_id')
            ->toArray();

        $actualCount = count($userIds);
        if ($actualCount === 0) {
            $this->warn("No users with stakes found.");
            return;
        }

        // 2. 删除这些用户的质押记录
        $deletedCount = DB::table('mg_user_contracts')
            ->whereIn('user_id', $userIds)
            ->delete();

        $this->info("Successfully removed {$deletedCount} stake records for {$actualCount} random users.");
        $this->warn("Note: Active status in 'users' table is NOT updated. Run 'mg:check-active-users' to sync.");
    }
}
