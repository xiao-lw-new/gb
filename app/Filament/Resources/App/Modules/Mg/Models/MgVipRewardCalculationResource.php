<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgVipRewardCalculationResource\Pages;
use App\Modules\Mg\Models\MgVipRewardCalculation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgVipRewardCalculationResource extends BaseResource
{
    protected static ?string $model = MgVipRewardCalculation::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = '收益审计';
    protected static ?string $label = 'VIP极差明细';
    protected static ?string $pluralLabel = 'VIP极差明细';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.address')
                    ->label('来源用户地址')
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . $search . '%'))),
                TextColumn::make('parentVipUser.address')
                    ->label('获利VIP地址')
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('parentVipUser', fn ($q) => $q->where('address', 'like', '%' . $search . '%'))),
                TextColumn::make('static_reward')->label('基准收益')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('pre_amount')->label('理论总额')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('distributed_amount')->label('已分配总额')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('actual_amount')->label('本次极差(U)')->sortable()->color('success')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('date_ref')->label('日期')->sortable(),
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
            'index' => Pages\ManageMgVipRewardCalculations::route('/'),
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
