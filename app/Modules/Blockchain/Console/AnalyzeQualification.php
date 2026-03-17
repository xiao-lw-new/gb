<?php

namespace App\Modules\Blockchain\Console;

use App\Models\User;
use App\Modules\Blockchain\Models\FoundationQualificationResult;
use App\Modules\Blockchain\Models\UserGoutInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeQualification extends Command
{
    protected $signature = 'blockchain:analyze-qualification';

    protected $description = 'Interactive tool to analyze foundation qualification status for a given address';

    public function handle(): int
    {
        while (true) {
            $address = $this->ask('请输入要查询的地址（输入 q 退出）');

            if ($address === null || strtolower(trim($address)) === 'q') {
                $this->info('退出。');
                return self::SUCCESS;
            }

            $address = strtolower(trim($address));
            $user = User::where('address', $address)->first();

            if (!$user) {
                $this->error("未找到该地址对应的用户: {$address}");
                $this->newLine();
                continue;
            }

            $this->analyzeUser($user);
            $this->newLine();
        }
    }

    private function analyzeUser(User $user): void
    {
        $gout = UserGoutInfo::where('user_id', $user->id)->first();
        $qual = FoundationQualificationResult::where('user_id', $user->id)->first();

        $this->newLine();
        $this->line("═══════════════════════════════════════════════════════");
        $this->line("  基金会达标分红分析报告");
        $this->line("═══════════════════════════════════════════════════════");
        $this->line("  地址: {$user->address}");
        $this->line("  用户ID: {$user->id}");
        $this->line("───────────────────────────────────────────────────────");

        if (!$gout) {
            $this->warn("  该用户没有 Gout 数据记录，无法分析。");
            return;
        }

        $totalBurn = (string) $gout->total_burn_amount;
        $threshold = bcmul($totalBurn, '0.1', 18);

        $this->info("  【个人数据】");
        $this->line("  基金会质押分红狗头数量:  {$this->fmt($totalBurn)}");
        $this->line("  达标阈值 (质押的10%):    {$this->fmt($threshold)}");
        $this->line("  个人持有狗头币:          {$this->fmt($gout->token_balance)}");
        $this->line("  个人燃烧狗头币:          {$this->fmt($gout->burn_amount_new)}");
        $this->line("  个人质押打新:            {$this->fmt($gout->new_gout_amount)}");

        $teamRow = DB::table('user_relations as ur')
            ->join('user_gout_info as ugi', 'ugi.user_id', '=', 'ur.user_id')
            ->where('ur.ancestor_id', $user->id)
            ->whereIn('ur.distance', [1, 2])
            ->selectRaw('SUM(ugi.token_balance) as token_balance, SUM(ugi.burn_amount_new) as burn_amount_new, SUM(ugi.new_gout_amount) as new_gout_amount')
            ->first();

        $teamToken = (string) ($teamRow?->token_balance ?? '0');
        $teamBurn = (string) ($teamRow?->burn_amount_new ?? '0');
        $teamNew = (string) ($teamRow?->new_gout_amount ?? '0');

        $teamMemberCount = DB::table('user_relations')
            ->where('ancestor_id', $user->id)
            ->whereIn('distance', [1, 2])
            ->count();

        $this->newLine();
        $this->info("  【伞下两代数据】(共 {$teamMemberCount} 人)");
        $this->line("  伞下持有狗头币合计:      {$this->fmt($teamToken)}");
        $this->line("  伞下燃烧狗头币合计:      {$this->fmt($teamBurn)}");
        $this->line("  伞下质押打新合计:        {$this->fmt($teamNew)}");

        $cond1Total = bcadd((string) $gout->token_balance, $teamToken, 18);
        $cond2Total = bcadd((string) $gout->burn_amount_new, $teamBurn, 18);
        $cond3Total = bcadd((string) $gout->new_gout_amount, $teamNew, 18);

        $cond1Met = bccomp($cond1Total, $threshold, 18) >= 0;
        $cond2Met = bccomp($cond2Total, $threshold, 18) >= 0;
        $cond3Met = bccomp($cond3Total, $threshold, 18) >= 0;

        if (bccomp($threshold, '0', 18) === 0) {
            $cond1Met = false;
            $cond2Met = false;
            $cond3Met = false;
        }

        $isQualified = $cond1Met || $cond2Met || $cond3Met;

        $this->newLine();
        $this->line("───────────────────────────────────────────────────────");
        $this->info("  【达标条件判断】(满足任意一个即达标)");
        $this->newLine();

        $this->printCondition(
            1,
            '自己+伞下两代 持有狗头币',
            $cond1Total,
            $threshold,
            $cond1Met
        );

        $this->printCondition(
            2,
            '自己+伞下两代 燃烧狗头币',
            $cond2Total,
            $threshold,
            $cond2Met
        );

        $this->printCondition(
            3,
            '自己+伞下两代 质押打新',
            $cond3Total,
            $threshold,
            $cond3Met
        );

        $this->line("───────────────────────────────────────────────────────");

        if (bccomp($totalBurn, '0', 18) === 0) {
            $this->warn("  结论: 该用户没有基金会质押分红狗头，不参与达标判断。");
        } elseif ($isQualified) {
            $this->info("  结论: 已达标! 可以获得基金会分红。");
        } else {
            $this->error("  结论: 未达标。");
            $this->newLine();
            $this->warn("  未达标原因:");

            $diff1 = bcsub($threshold, $cond1Total, 18);
            $diff2 = bcsub($threshold, $cond2Total, 18);
            $diff3 = bcsub($threshold, $cond3Total, 18);

            if (bccomp($diff1, '0', 18) > 0) {
                $this->line("    条件1: 持有狗头币还差 {$this->fmt($diff1)} 才能达标");
            }
            if (bccomp($diff2, '0', 18) > 0) {
                $this->line("    条件2: 燃烧狗头币还差 {$this->fmt($diff2)} 才能达标");
            }
            if (bccomp($diff3, '0', 18) > 0) {
                $this->line("    条件3: 质押打新还差 {$this->fmt($diff3)} 才能达标");
            }

            $this->newLine();
            $this->line("  提示: 以上任意一个条件补足差额即可达标。");
            $this->line("  可以通过自己购买/燃烧/打新，或发展伞下两代来达成。");
        }

        $this->line("═══════════════════════════════════════════════════════");

        if ($qual) {
            $this->line("  最后检查时间: {$qual->checked_at}");
        }
    }

    private function printCondition(int $num, string $name, string $value, string $threshold, bool $met): void
    {
        $icon = $met ? '  [PASS]' : '  [FAIL]';
        $status = $met ? '达标' : '未达标';
        $this->line("  条件{$num}: {$name}");
        $this->line("    合计: {$this->fmt($value)} / 需要: {$this->fmt($threshold)}");
        $this->line("    {$icon} {$status}");
        $this->newLine();
    }

    private function fmt(string $value): string
    {
        $parts = explode('.', $value);
        $integer = $parts[0];
        $decimal = isset($parts[1]) ? rtrim($parts[1], '0') : '';

        $formatted = number_format((float) $integer, 0, '', ',');
        if ($decimal !== '') {
            $formatted .= '.' . substr($decimal, 0, 4);
        }

        return $formatted;
    }
}
