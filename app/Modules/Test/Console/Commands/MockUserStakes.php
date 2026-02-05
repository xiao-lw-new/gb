<?php

namespace App\Modules\Test\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class MockUserStakes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mock-stakes {--ratio=80 : Percentage of users to add stakes to}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Randomly add stakes (100-2000 U) to a percentage of users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ratio = (int) $this->option('ratio');
        if ($ratio <= 0 || $ratio > 100) {
            $this->error("Ratio must be between 1 and 100.");
            return;
        }

        $totalUsers = User::where('id', '>', 1)->count();
        if ($totalUsers === 0) {
            $this->warn("No users found to add stakes to.");
            return;
        }

        $targetCount = (int) ($totalUsers * ($ratio / 100));
        $this->info("Target: Adding stakes to {$targetCount} users ({$ratio}% of {$totalUsers} users)...");

        $now = Carbon::now();
        $batchSize = 1000;
        $processed = 0;

        $bar = $this->output->createProgressBar($targetCount);
        $bar->start();

        // 使用 chunk 遍历用户，避免大查询卡死
        User::where('id', '>', 1)
            ->orderBy('id')
            ->chunk(2000, function ($users) use (&$processed, $targetCount, $ratio, $now, $bar, $batchSize) {
                $stakeBuffer = [];

                foreach ($users as $user) {
                    if ($processed >= $targetCount) {
                        return false; // 停止 chunk
                    }

                    // 随机判定是否给该用户加质押
                    if (rand(1, 100) <= $ratio) {
                        $uAmount = rand(100, 2000);
                        // 假设 1U = 10 Token，按比例填充 amount
                        $tokenAmount = $uAmount * 10;
                        $stakeType = rand(1, 2); // 随机 7天(1) 或 15天(2)

                        $stakeBuffer[] = [
                            'user_id'    => $user->id,
                            'stakeType'  => $stakeType,
                            'u_amount'   => number_format($uAmount, 18, '.', ''),
                            'amount'     => number_format($tokenAmount, 18, '.', ''),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $processed++;
                        $bar->advance();

                        if (count($stakeBuffer) >= $batchSize) {
                            DB::table('mg_user_contracts')->insert($stakeBuffer);
                            $stakeBuffer = [];
                        }
                    }
                }

                if (!empty($stakeBuffer)) {
                    DB::table('mg_user_contracts')->insert($stakeBuffer);
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("Successfully added stakes to {$processed} users.");
        $this->warn("Note: This command only inserts data into 'mg_user_contracts'. You may need to run stats rebuild commands to update VIP performance.");
    }
}
