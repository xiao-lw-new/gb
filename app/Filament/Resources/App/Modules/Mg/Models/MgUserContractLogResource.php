<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgUserContractLogResource\Pages;
use App\Modules\Mg\Models\MgUserContractLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgUserContractLogResource extends BaseResource
{
    protected static ?string $model = MgUserContractLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = '质押体系';
    protected static ?string $label = '质押流水';
    protected static ?string $pluralLabel = '质押流水';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('用户ID')->sortable()->searchable(),
                TextColumn::make('stakeType')->label('类型')->formatStateUsing(fn ($state) => $state == 1 ? '7天' : '15天'),
                TextColumn::make('record_idx')->label('IDX'),
                TextColumn::make('u_amount')->label('U价值变动')->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('amount')->label('代币变动')->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('transaction_hash')->label('交易哈希')->limit(10)->copyable(),
                TextColumn::make('block_time')->label('区块时间')->dateTime(),
            ])
            ->filters([
                //
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
            'index' => Pages\ManageMgUserContractLogs::route('/'),
        ];
    }
}
