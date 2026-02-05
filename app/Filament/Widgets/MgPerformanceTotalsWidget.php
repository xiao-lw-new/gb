<?php

namespace App\Filament\Widgets;

use App\Modules\Mg\Services\MgDashboardMetricsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MgPerformanceTotalsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $service = app(MgDashboardMetricsService::class);
        $filters = $service->normalizeFilters($this->filters);
        $totals = $service->getPerformanceTotals($filters);

        return [
            Stat::make('总质押数量(U)', $this->formatDecimal($totals['stake_total'] ?? '0')),
            Stat::make('总解质押数量(U)', $this->formatDecimal($totals['unstake_total'] ?? '0')),
            Stat::make('总额度购买数量(U)', $this->formatDecimal($totals['quota_buy_total'] ?? '0')),
            Stat::make('总额度扣除数量(U)', $this->formatDecimal($totals['quota_consume_total'] ?? '0')),
            Stat::make('涡轮池转入总量(U)', $this->formatDecimal($totals['turbine_in_total'] ?? '0')),
            Stat::make('涡轮池转出总量(U)', $this->formatDecimal($totals['turbine_out_total'] ?? '0')),
        ];
    }

    protected function formatDecimal(string $value): string
    {
        return number_format((float) $value, 4, '.', ',');
    }
}
