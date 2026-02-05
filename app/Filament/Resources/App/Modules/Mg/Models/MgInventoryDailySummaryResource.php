<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\App\Modules\Mg\Models\MgInventoryDailySummaryResource\Pages;
use App\Filament\Resources\BaseResource;
use App\Modules\Mg\Models\MgInventoryDailySummary;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class MgInventoryDailySummaryResource extends BaseResource
{
    protected static ?string $model = MgInventoryDailySummary::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = '数据盘点';
    protected static ?string $label = '盘点汇总';
    protected static ?string $pluralLabel = '盘点汇总';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('日期')->sortable(),
                TextColumn::make('user_type')->label('用户类型'),
                TextColumn::make('stake_amount')->label('当日质押')->numeric(4),
                TextColumn::make('quota_buy_amount')->label('当日购买额度')->numeric(4),
                TextColumn::make('turbine_buy_amount')->label('当日购买涡轮')->numeric(4),
                TextColumn::make('redeem_fee_amount')->label('当日赎回手续费')->numeric(4),
                TextColumn::make('redeem_amount')->label('当日赎回所得')->numeric(4),
                TextColumn::make('turbine_sell_amount')->label('当日卖出涡轮所得')->numeric(4),
                TextColumn::make('reward_claim_amount')->label('当日领取收益')->numeric(4),
                TextColumn::make('daily_inflow')->label('日净入金')->numeric(4),
                TextColumn::make('total_inflow')->label('累计净入金')->numeric(4),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_type')
                    ->options([
                        'all' => '全部',
                        'normal' => '普通用户',
                        'robot' => '机器人用户',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('开始日期'),
                        Forms\Components\DatePicker::make('until')->label('结束日期'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date));
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function canEdit($record): bool { return false; } public static function canDelete($record): bool { return false; } public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMgInventoryDailySummaries::route('/'),
        ];
    }
}

