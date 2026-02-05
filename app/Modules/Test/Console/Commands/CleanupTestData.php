<?php

namespace App\Modules\Test\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanupTestData extends Command
{
    protected $signature = 'test:cleanup-data {--all : Delete all users except ID 1}';
    protected $description = 'Cleanup mocked test data from the database';

    public function handle()
    {
        if (!$this->confirm('Are you sure you want to delete test data? This action cannot be undone!')) {
            return;
        }

        $remarks = ['Test generated user', 'Bulk test user', 'Bulk optimized user'];

        DB::transaction(function () use ($remarks) {
            $query = User::where('id', '>', 1);

            if (!$this->option('all')) {
                $query->whereIn('remark', $remarks);
            }

            $userIds = $query->pluck('id')->toArray();
            $count = count($userIds);

            if ($count === 0) {
                $this->info("No test data found to cleanup.");
                return;
            }

            $this->info("Cleaning up $count users and their related data...");

            // 1. 删除关联表数据
            DB::table('mg_user_contracts')->whereIn('user_id', $userIds)->delete();
            DB::table('mg_user_contract_logs')->whereIn('user_id', $userIds)->delete();
            DB::table('mg_user_quotas')->whereIn('user_id', $userIds)->delete();
            DB::table('mg_user_quota_logs')->whereIn('user_id', $userIds)->delete();
            DB::table('mg_vip_performance')->whereIn('user_id', $userIds)->delete();
            DB::table('mg_vip_levels')->whereIn('user_id', $userIds)->delete();
            DB::table('mg_user_daily_rewards')->whereIn('user_id', $userIds)->delete();
            
            // 2. 删除用户表
            DB::table('users')->whereIn('id', $userIds)->delete();

            // 3. 重置根节点的统计 (可选)
            DB::table('mg_vip_performance')->where('user_id', 1)->update([
                'direct_count' => 0,
                'team_count' => 0,
                'direct_active_count' => 0,
                'team_active_count' => 0,
                'total_performance' => '0',
            ]);

            $this->info("Successfully deleted $count users.");
        });
    }
}
