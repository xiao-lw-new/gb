<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Mg\Models\MgUserContract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedStakingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:staking {--topup-only : Only run the top-up step for existing staked users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Randomly seed staking data for 80% of users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('topup-only')) {
            $this->info('Step 1: Seeding initial staking data (80% of users)...');

            $totalUsers = User::count();
            if ($totalUsers == 0) {
                $this->error('No users found.');
                return;
            }

            // 1. Get random 80% users
            $users = User::inRandomOrder()->take((int)($totalUsers * 0.8))->get();
            $this->info('Selected ' . $users->count() . ' users for initial staking.');

            $bar = $this->output->createProgressBar($users->count());
            $bar->start();

            foreach ($users as $user) {
                // 2. Random amount 100-1000
                $amount = rand(100, 1000);

                // 3. Random duration 7 or 15 days
                // Assuming stakeType 1 = 7 days, 2 = 15 days
                $stakeType = rand(0, 1) ? 1 : 2; 

                $contract = MgUserContract::firstOrNew([
                    'user_id' => $user->id,
                    'stakeType' => $stakeType
                ]);

                $contract->u_amount = ($contract->u_amount ?? 0) + $amount;
                $contract->amount = ($contract->amount ?? 0) + $amount;
                
                $contract->save();

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Initial staking data seeded.');
        } else {
            $this->info('Skipping Step 1 (Initial Seeding)...');
        }

        // Step 2: Give 1/3 of people with pledges an increase in quota of 100~1000
        $this->info('Step 2: Adding MgUserQuota to 1/3 of staked users...');
        
        $stakedUserIds = MgUserContract::distinct()->pluck('user_id');
        $countStaked = $stakedUserIds->count();
        
        if ($countStaked > 0) {
            $usersToTopUp = $stakedUserIds->random((int)ceil($countStaked / 3));
            $this->info('Selected ' . $usersToTopUp->count() . ' staked users for quota top-up.');
            
            $bar2 = $this->output->createProgressBar($usersToTopUp->count());
            $bar2->start();

            foreach ($usersToTopUp as $userId) {
                $amount = rand(100, 1000);
                
                $quota = \App\Modules\Mg\Models\MgUserQuota::firstOrNew(['user_id' => $userId]);
                $quota->quota = ($quota->quota ?? 0) + $amount;
                $quota->cumulative_quota = ($quota->cumulative_quota ?? 0) + $amount;
                $quota->save();

                $bar2->advance();
            }
            $bar2->finish();
            $this->newLine();
        } else {
            $this->info('No staked users found to top up.');
        }

        $this->info('Staking and Quota seeding completed successfully.');
    }
}
