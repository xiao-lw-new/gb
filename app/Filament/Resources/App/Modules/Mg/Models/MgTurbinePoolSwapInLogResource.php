<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgTurbinePoolSwapInLogResource\Pages;
use App\Modules\Mg\Models\MgTurbinePoolSwapInLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgTurbinePoolSwapInLogResource extends BaseResource
{
    protected static ?string $model = MgTurbinePoolSwapInLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-on-rectangle';
    protected static ?string $navigationGroup = '涡轮池监控';
    protected static ?string $label = '转入记录(SwapIn)';
    protected static ?string $pluralLabel = '转入记录(SwapIn)';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('用户ID')->sortable()->searchable(),
                TextColumn::make('in_amount')->label('转入数量')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('in_claim_quota')->label('转入消耗额度')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('in_worth')->label('转入价值(U)')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('mx_amount')->label('获得MX数量')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('transaction_hash')->label('交易哈希')->limit(10)->copyable(),
                TextColumn::make('block_time')->label('时间')->dateTime(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    private static function formatDecimal($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return sprintf('%.4f', (float) $value);
    }

    public static function canEdit($record): bool { return false; } public static function canDelete($record): bool { return false; } public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMgTurbinePoolSwapInLogs::route('/'),
        ];
    }
}
