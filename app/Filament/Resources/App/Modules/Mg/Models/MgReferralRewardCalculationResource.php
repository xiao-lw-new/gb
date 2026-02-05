<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgReferralRewardCalculationResource\Pages;
use App\Modules\Mg\Models\MgReferralRewardCalculation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgReferralRewardCalculationResource extends BaseResource
{
    protected static ?string $model = MgReferralRewardCalculation::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = '收益审计';
    protected static ?string $label = '推荐奖明细';
    protected static ?string $pluralLabel = '推荐奖明细';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.address')
                    ->label('获利用户地址')
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . $search . '%'))),
                TextColumn::make('sourceUser.address')
                    ->label('来源用户地址')
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('sourceUser', fn ($q) => $q->where('address', 'like', '%' . $search . '%'))),
                TextColumn::make('generation')->label('代数')->sortable(),
                TextColumn::make('ratio')
                    ->label('比例')
                    ->formatStateUsing(fn ($state) => sprintf('%.4f', (float) $state)),
                TextColumn::make('source_amount')
                    ->label('基准金额')
                    ->formatStateUsing(fn ($state) => sprintf('%.4f', (float) $state)),
                TextColumn::make('amount')
                    ->label('奖励金额(U)')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => sprintf('%.4f', (float) $state)),
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
            'index' => Pages\ManageMgReferralRewardCalculations::route('/'),
        ];
    }
}
