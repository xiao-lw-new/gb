<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserInventoryDailyResource;
use App\Modules\Mg\Models\MgUserInventoryDaily;
use App\Modules\Mg\Services\MgDashboardMetricsService;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class MgInventoryDailyLatestUsersWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '用户盘点（最近一天）';
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->buildQuery())
            ->columns([
                TextColumn::make('date')->label('日期'),
                TextColumn::make('user.address')->label('用户地址')->limit(12)->copyable(),
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
                    ->url(fn () => MgUserInventoryDailyResource::getUrl('index'))
                    ->openUrlInNewTab(),
            ]);
    }

    private function buildQuery()
    {
        $service = app(MgDashboardMetricsService::class);
        $filters = $service->normalizeFilters($this->filters);
        $channel = $filters['channel'] ?? 'all';

        $latestDate = MgUserInventoryDaily::query()->max('date');
        if (! $latestDate) {
            return MgUserInventoryDaily::query()->whereRaw('1=0');
        }

        $query = MgUserInventoryDaily::query()->whereDate('date', $latestDate);
        if ($channel === 'robot') {
            $query->where('user_type', 'robot');
        } elseif ($channel === 'normal') {
            $query->where('user_type', 'normal');
        }

        return $query;
    }
}

