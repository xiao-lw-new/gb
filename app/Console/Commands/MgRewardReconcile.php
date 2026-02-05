<?php

namespace App\Console\Commands;

use App\Modules\Mg\Models\MgReferralDailyReward;
use App\Modules\Mg\Models\MgReferralRewardCalculation;
use App\Modules\Mg\Models\MgUserDailyReward;
use App\Modules\Mg\Models\MgVipDailyReward;
use App\Modules\Mg\Models\MgVipRewardCalculation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MgRewardReconcile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mg:reward-reconcile {userId} {date} {--json : Output as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '对账：输出指定用户在指定日期的静态/推荐/VIP收益汇总与明细合计';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('userId');
        $date = (string) $this->argument('date');

        $staticDaily = MgUserDailyReward::where('user_id', $userId)->where('date_ref', $date)->first();
        $refDaily = MgReferralDailyReward::where('user_id', $userId)->where('date_ref', $date)->first();
        $vipDaily = MgVipDailyReward::where('parent_vip_id', $userId)->where('date_ref', $date)->first();

        $refDetailSum = (string) (MgReferralRewardCalculation::where('user_id', $userId)->where('date_ref', $date)->sum('amount') ?: '0');
        $vipDetailSum = (string) (MgVipRewardCalculation::where('parent_vip_id', $userId)->where('date_ref', $date)->sum('actual_amount') ?: '0');

        $flow = DB::table('mg_user_reward_flow')
            ->where('user_id', $userId)
            ->where('date_ref', $date)
            ->selectRaw("type, SUM(amount) as total")
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $payload = [
            'user_id' => $userId,
            'date' => $date,
            'static' => [
                'daily' => $staticDaily ? $staticDaily->toArray() : null,
                'flow_total' => (string) ($flow['static'] ?? '0'),
            ],
            'referral' => [
                'daily' => $refDaily ? $refDaily->toArray() : null,
                'detail_sum' => $refDetailSum,
                'flow_total' => (string) ($flow['referral'] ?? '0'),
            ],
            'vip' => [
                'daily' => $vipDaily ? $vipDaily->toArray() : null,
                'detail_sum' => $vipDetailSum,
                'flow_total' => (string) ($flow['community'] ?? '0'),
            ],
        ];

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return;
        }

        $this->info("对账结果 user_id={$userId}, date={$date}");
        $this->line("静态: daily.actual=" . ($staticDaily->actual_amount ?? '0') . " | flow.static=" . ($payload['static']['flow_total']));
        $this->line("推荐: daily.actual=" . ($refDaily->actual_amount ?? '0') . " | detail.sum=" . $refDetailSum . " | flow.referral=" . ($payload['referral']['flow_total']));
        $this->line("VIP : daily.actual=" . ($vipDaily->actual_amount ?? '0') . " | detail.sum=" . $vipDetailSum . " | flow.community=" . ($payload['vip']['flow_total']));
    }
}
