<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserInventoryDailyResource\Pages;
use App\Filament\Resources\BaseResource;
use App\Modules\Mg\Models\MgUserInventoryDaily;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class MgUserInventoryDailyResource extends BaseResource
{
    protected static ?string $model = MgUserInventoryDaily::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = '数据盘点';
    protected static ?string $label = '用户盘点';
    protected static ?string $pluralLabel = '用户盘点';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('日期')->sortable(),
                TextColumn::make('user.address')
                    ->label('用户地址')
                    ->copyable()
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . strtolower(trim($search)) . '%'))),
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
                TextColumn::make('stake_amount_total')->label('累计质押')->numeric(4),
                TextColumn::make('quota_buy_amount_total')->label('累计购买额度')->numeric(4),
                TextColumn::make('turbine_buy_amount_total')->label('累计购买涡轮')->numeric(4),
                TextColumn::make('redeem_fee_amount_total')->label('累计赎回手续费')->numeric(4),
                TextColumn::make('redeem_amount_total')->label('累计赎回所得')->numeric(4),
                TextColumn::make('turbine_sell_amount_total')->label('累计卖出涡轮所得')->numeric(4),
                TextColumn::make('reward_claim_amount_total')->label('累计领取收益')->numeric(4),
            ])
            ->filters([
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
                Tables\Filters\Filter::make('user_address')
                    ->form([
                        Forms\Components\TextInput::make('address')->label('用户地址'),
                    ])
                    ->query(function ($query, array $data) {
                        $addr = strtolower(trim((string) ($data['address'] ?? '')));
                        if ($addr === '') {
                            return;
                        }
                        $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . $addr . '%'));
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
            'index' => Pages\ManageMgUserInventoryDailies::route('/'),
        ];
    }
}

