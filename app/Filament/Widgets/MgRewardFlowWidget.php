<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserRewardFlowResource;
use App\Modules\Mg\Models\MgUserRewardFlow;
use App\Modules\Mg\Services\MgDashboardMetricsService;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MgRewardFlowWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '静动态收益';
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('user_address')
                    ->label('用户地址')
                    ->limit(12)
                    ->copyable(),
                TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ((string) $state) {
                        'static' => '静态',
                        'referral' => '推荐',
                        'community' => 'VIP',
                        default => (string) $state,
                    }),
                TextColumn::make('amount')
                    ->label('实发(U)')
                    ->formatStateUsing(fn ($state) => sprintf('%.4f', (float) $state)),
                TextColumn::make('expected_amount')
                    ->label('应得(U)')
                    ->formatStateUsing(fn ($state) => sprintf('%.4f', (float) $state)),
                IconColumn::make('need_quota')
                    ->label('额度不足')
                    ->boolean(),
                TextColumn::make('date_ref')
                    ->label('收益日期'),
            ])
            ->defaultSort('date_ref', 'desc')
            ->paginationPageOptions([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                TableAction::make('more')
                    ->label('更多')
                    ->url(fn () => MgUserRewardFlowResource::getUrl('index'))
                    ->openUrlInNewTab(),
            ]);
    }

    protected function getQuery(): Builder
    {
        $service = app(MgDashboardMetricsService::class);
        $filters = $service->normalizeFilters($this->filters);

        $table = (new MgUserRewardFlow())->getTable();
        $query = MgUserRewardFlow::query()
            ->from($table)
            ->select([
                "{$table}.*",
                'users.address as user_address',
            ])
            ->join('users', 'users.id', '=', "{$table}.user_id")
            ->whereIn("{$table}.type", ['static', 'referral', 'community']);

        $service->applyUserFilterOnJoined($query, $filters, $table);
        $service->applyChannelFilterOnJoined($query, $filters);
        $service->applyDateRange($query, $filters, "{$table}.date_ref");

        return $query;
    }
}
