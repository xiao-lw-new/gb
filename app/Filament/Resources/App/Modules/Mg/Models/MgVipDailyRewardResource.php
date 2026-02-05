<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgVipDailyRewardResource\Pages;
use App\Modules\Mg\Models\MgVipDailyReward;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgVipDailyRewardResource extends BaseResource
{
    protected static ?string $model = MgVipDailyReward::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = '收益审计';
    protected static ?string $label = 'VIP奖励汇总';
    protected static ?string $pluralLabel = 'VIP奖励汇总';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parentVipUser.address')
                    ->label('获利VIP地址')
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('parentVipUser', fn ($q) => $q->where('address', 'like', '%' . $search . '%'))),
                TextColumn::make('date_ref')->label('日期')->sortable(),
                TextColumn::make('expected_amount')->label('预计应得(U)')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('actual_amount')->label('实际获得(U)')->sortable()->color('success')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('updated_at')->label('发放时间')->dateTime(),
            ])
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
            'index' => Pages\ManageMgVipDailyRewards::route('/'),
        ];
    }

    private static function formatDecimal($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return sprintf('%.4f', (float) $value);
    }
}
