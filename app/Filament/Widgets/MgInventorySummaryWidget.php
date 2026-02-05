<?php

namespace App\Filament\Widgets;

use App\Modules\Mg\Services\MgDashboardMetricsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MgInventorySummaryWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $service = app(MgDashboardMetricsService::class);
        $filters = $service->normalizeFilters($this->filters);
        $totals = $service->getInventoryTotals($filters);

        return [
            Stat::make('质押数量', $this->formatDecimal($totals['stake_total'] ?? $totals['stake_amount'] ?? '0'))
                ->color('danger'),
            Stat::make('购买额度数量', $this->formatDecimal($totals['quota_buy_total'] ?? $totals['quota_buy_amount'] ?? '0'))
                ->color('danger'),
            Stat::make('购买涡轮数量', $this->formatDecimal($totals['turbine_buy_total'] ?? $totals['turbine_buy_amount'] ?? '0'))
                ->color('danger'),
            Stat::make('赎回手续费', $this->formatDecimal($totals['redeem_fee_total'] ?? $totals['redeem_fee_amount'] ?? '0'))
                ->color('danger'),
            Stat::make('赎回实际所得', $this->formatDecimal($totals['redeem_amount_total'] ?? $totals['redeem_amount'] ?? '0'))
                ->color('success'),
            Stat::make('卖出涡轮所得', $this->formatDecimal($totals['turbine_sell_total'] ?? $totals['turbine_sell_amount'] ?? '0'))
                ->color('success'),
            Stat::make('领取收益所得', $this->formatDecimal($totals['reward_claim_total'] ?? $totals['reward_claim_amount'] ?? '0'))
                ->color('success'),
            Stat::make('日净入金', $this->formatDecimal($totals['daily_inflow'] ?? '0')),
            Stat::make('累计净入金', $this->formatDecimal($totals['total_inflow'] ?? '0')),
        ];
    }

    private function formatDecimal(string $value): string
    {
        return number_format((float) $value, 4, '.', ',');
    }

}

