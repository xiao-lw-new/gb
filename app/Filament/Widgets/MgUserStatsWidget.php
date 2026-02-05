<?php

namespace App\Filament\Widgets;

use App\Modules\Mg\Services\MgDashboardMetricsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MgUserStatsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $service = app(MgDashboardMetricsService::class);
        $filters = $service->normalizeFilters($this->filters);
        $stats = $service->getUserStats($filters);

        return [
            Stat::make('新增会员数', $this->formatNumber($stats['new'] ?? '0'))
                ->description($this->dateRangeLabel($filters)),
            Stat::make('总会员数', $this->formatNumber($stats['total'] ?? '0')),
        ];
    }

    protected function dateRangeLabel(array $filters): string
    {
        $start = $filters['start']->toDateString();
        $end = $filters['end']->toDateString();
        return $start === $end ? $start : "{$start} ~ {$end}";
    }

    protected function formatNumber(string $value): string
    {
        return number_format((float) $value, 0, '.', ',');
    }
}
