<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\App\Modules\Mg\Models\MgInventoryDailySummaryResource;
use App\Modules\Mg\Models\MgInventoryDailySummary;
use App\Modules\Mg\Services\MgDashboardMetricsService;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class MgInventoryDailySummaryRecentWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '盘点汇总（最近10天）';
    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->buildQuery())
            ->columns([
                TextColumn::make('date')->label('日期'),
                TextColumn::make('user_type')
                    ->label('类型')
                    ->formatStateUsing(fn ($state) => $state === 'robot' ? '机器人' : ($state === 'normal' ? '普通' : '全部')),
                TextColumn::make('daily_inflow')
                    ->label('日净入金')
                    ->numeric(4),
                TextColumn::make('total_inflow')
                    ->label('累计净入金')
                    ->numeric(4),
            ])
            ->defaultSort('date', 'desc')
            ->paginationPageOptions([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                TableAction::make('more')
                    ->label('更多')
                    ->url(fn () => MgInventoryDailySummaryResource::getUrl('index'))
                    ->openUrlInNewTab(),
            ]);
    }

    private function buildQuery()
    {
        $service = app(MgDashboardMetricsService::class);
        $filters = $service->normalizeFilters($this->filters);
        $channel = $filters['channel'] ?? 'all';

        return MgInventoryDailySummary::query()
            ->when($channel !== 'all', fn ($q) => $q->where('user_type', $channel))
            ->orderByDesc('date')
            ->limit(10);
    }
}

