<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgUserQuotaResource\Pages;
use App\Modules\Mg\Models\MgUserQuota;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgUserQuotaResource extends BaseResource
{
    protected static ?string $model = MgUserQuota::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = '收益配额';
    protected static ?string $label = '收益额度';
    protected static ?string $pluralLabel = '收益额度';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('用户ID')->sortable()->searchable(),
                TextColumn::make('quota')->label('当前剩余额度')->sortable(),
                TextColumn::make('cumulative_quota')->label('累计总额度')->sortable(),
                TextColumn::make('updated_at')->label('更新时间')->dateTime(),
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
            'index' => Pages\ManageMgUserQuotas::route('/'),
        ];
    }
}
