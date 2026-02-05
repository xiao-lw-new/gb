<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgTurbinePoolSwapOutLogResource\Pages;
use App\Modules\Mg\Models\MgTurbinePoolSwapOutLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgTurbinePoolSwapOutLogResource extends BaseResource
{
    protected static ?string $model = MgTurbinePoolSwapOutLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-on-rectangle';
    protected static ?string $navigationGroup = '涡轮池监控';
    protected static ?string $label = '转出记录(SwapOut)';
    protected static ?string $pluralLabel = '转出记录(SwapOut)';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('用户ID')->sortable()->searchable(),
                TextColumn::make('out_mx_amount')->label('转出MX数量')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('out_worth')->label('转出价值(U)')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('fee')->label('手续费')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('back_worth')->label('返回价值(U)')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('cur_mx_amount')->label('当前MX总量')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('cur_worth')->label('当前总价值(U)')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
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
            'index' => Pages\ManageMgTurbinePoolSwapOutLogs::route('/'),
        ];
    }
}
