<?php

namespace App\Modules\Blockchain\Services;

use App\Modules\Blockchain\Models\FoundationQualificationResult;
use App\Modules\Blockchain\Models\UserGoutInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FoundationQualificationService
{
    private const THRESHOLD_RATIO = '0.1';
    private const LOG_CHANNEL = 'foundation_qualification';

    public function check(): void
    {
        $allGoutInfo = UserGoutInfo::all()->keyBy('user_id');

        if ($allGoutInfo->isEmpty()) {
            Log::channel(self::LOG_CHANNEL)->info('[Qualification]: No user_gout_info records found.');
            return;
        }

        $teamData = $this->getTeamAggregates();

        $now = now();
        $qualifiedCount = 0;
        $totalCount = 0;

        foreach ($allGoutInfo as $userId => $info) {
            $totalCount++;

            try {
                $result = $this->evaluate($userId, $info, $teamData[$userId] ?? null);

                FoundationQualificationResult::updateOrCreate(
                    ['user_id' => $userId],
                    array_merge($result, ['checked_at' => $now])
                );

                $info->is_qualified = $result['is_qualified'];
                $info->save();

                if ($result['is_qualified']) {
                    $qualifiedCount++;
                }
            } catch (\Throwable $e) {
                Log::channel(self::LOG_CHANNEL)->error("[Qualification]: Failed for user_id={$userId}: {$e->getMessage()}");
            }
        }

        Log::channel(self::LOG_CHANNEL)->info("[Qualification]: Completed. Total={$totalCount}, Qualified={$qualifiedCount}");
    }

    /**
     * 批量聚合所有用户的伞下 2 代团队数据
     */
    private function getTeamAggregates(): array
    {
        $rows = DB::table('user_relations as ur')
            ->join('user_gout_info as ugi', 'ugi.user_id', '=', 'ur.user_id')
            ->whereIn('ur.distance', [1, 2])
            ->groupBy('ur.ancestor_id')
            ->select([
                'ur.ancestor_id',
                DB::raw('SUM(ugi.token_balance) as team_token_balance'),
                DB::raw('SUM(ugi.burn_amount_new) as team_burn_amount'),
                DB::raw('SUM(ugi.new_gout_amount) as team_new_gout_amount'),
            ])
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->ancestor_id] = [
                'team_token_balance' => (string) $row->team_token_balance,
                'team_burn_amount' => (string) $row->team_burn_amount,
                'team_new_gout_amount' => (string) $row->team_new_gout_amount,
            ];
        }

        return $map;
    }

    /**
     * 评估单个用户的 3 个达标条件（每个条件都是自己 + 伞下两代的合计）
     */
    private function evaluate(int $userId, UserGoutInfo $info, ?array $team): array
    {
        $totalBurn = (string) $info->total_burn_amount;
        $threshold = bcmul($totalBurn, self::THRESHOLD_RATIO, 18);

        $teamTokenBalance = $team['team_token_balance'] ?? '0';
        $teamBurnAmount = $team['team_burn_amount'] ?? '0';
        $teamNewGoutAmount = $team['team_new_gout_amount'] ?? '0';

        // 条件1: 自己 + 伞下两代 持有狗头币合计 >= 阈值
        $cond1Value = bcadd((string) $info->token_balance, $teamTokenBalance, 18);
        $cond1Met = bccomp($cond1Value, $threshold, 18) >= 0;

        // 条件2: 自己 + 伞下两代 燃烧狗头币合计 >= 阈值
        $cond2Value = bcadd((string) $info->burn_amount_new, $teamBurnAmount, 18);
        $cond2Met = bccomp($cond2Value, $threshold, 18) >= 0;

        // 条件3: 自己 + 伞下两代 质押打新合计 >= 阈值
        $cond3Value = bcadd((string) $info->new_gout_amount, $teamNewGoutAmount, 18);
        $cond3Met = bccomp($cond3Value, $threshold, 18) >= 0;

        $isQualified = $cond1Met || $cond2Met || $cond3Met;

        // 0=未达标, 1=已达标, 2=未参与（无基金会质押）
        if (bccomp($threshold, '0', 18) === 0) {
            $qualifiedValue = 2;
        } else {
            $qualifiedValue = $isQualified ? 1 : 0;
        }

        return [
            'threshold' => $threshold,
            'cond1_value' => $cond1Value,
            'cond1_met' => $cond1Met ? 1 : 0,
            'cond2_value' => $cond2Value,
            'cond2_met' => $cond2Met ? 1 : 0,
            'cond3_value' => $cond3Value,
            'cond3_met' => $cond3Met ? 1 : 0,
            'is_qualified' => $qualifiedValue,
        ];
    }
}
