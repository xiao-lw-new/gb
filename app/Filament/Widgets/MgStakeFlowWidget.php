<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserContractLogResource;
use App\Modules\Mg\Models\MgUserContractLog;
use App\Modules\Mg\Services\MgDashboardMetricsService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MgStakeFlowWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '质押流水';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('user_address')
                    ->label('用户地址')
                    ->limit(12)
                    ->copyable(),
                TextColumn::make('stakeType')
                    ->label('类型')
                    ->formatStateUsing(fn ($state) => (int) $state === 1 ? '7天' : '15天'),
                TextColumn::make('u_amount')
                    ->label('U价值变动')
                    ->color(fn ($state) => (float) $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => sprintf('%.4f', (float) $state)),
                TextColumn::make('transaction_hash')
                    ->label('交易哈希')
                    ->limit(10)
                    ->copyable(),
                TextColumn::make('block_time')
                    ->label('区块时间')
                    ->dateTime('Y-m-d H:i:s'),
            ])
            ->defaultSort('block_time', 'desc')
            ->paginationPageOptions([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                TableAction::make('more')
                    ->label('更多')
                    ->url(fn () => MgUserContractLogResource::getUrl('index'))
                    ->openUrlInNewTab(),
            ]);
    }

    protected function getQuery(): Builder
    {
        $service = app(MgDashboardMetricsService::class);
        $filters = $service->normalizeFilters($this->filters);

        $table = (new MgUserContractLog())->getTable();
        $query = MgUserContractLog::query()
            ->from($table)
            ->select([
                "{$table}.*",
                'users.address as user_address',
            ])
            ->join('users', 'users.id', '=', "{$table}.user_id");

        $service->applyUserFilterOnJoined($query, $filters, $table);
        $service->applyChannelFilterOnJoined($query, $filters);
        $service->applyTimeRange($query, $filters, "{$table}.block_time");

        return $query;
    }
}
