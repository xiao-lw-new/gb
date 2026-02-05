<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgVipLevelResource\Pages;
use App\Modules\Mg\Models\MgVipLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgVipLevelResource extends BaseResource
{
    protected static ?string $model = MgVipLevel::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = '用户中心';
    protected static ?string $label = 'VIP等级管理';
    protected static ?string $pluralLabel = 'VIP等级管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')->disabled(),
                Forms\Components\Select::make('vip_level')
                    ->label('VIP等级')
                    ->options([
                        0 => '无',
                        1 => 'V1',
                        2 => 'V2',
                        3 => 'V3',
                        4 => 'V4',
                        5 => 'V5',
                        6 => 'V6',
                        7 => 'V7',
                    ]),
                Forms\Components\TextInput::make('vip_ratio')
                    ->label('收益比例')
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->label('晋升类型')
                    ->options([
                        0 => '自动升级',
                        1 => '手动锁定',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('用户ID')->sortable()->searchable(),
                TextColumn::make('vip_level')->label('等级')->sortable()->formatStateUsing(fn ($state) => "V{$state}"),
                TextColumn::make('vip_ratio')->label('比例')->sortable()->formatStateUsing(fn ($state) => ($state * 100) . '%'),
                TextColumn::make('type')->label('类型')->formatStateUsing(fn ($state) => $state == 0 ? '自动' : '手动'),
                TextColumn::make('updated_at')->label('更新时间')->dateTime(),
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
            'index' => Pages\ManageMgVipLevels::route('/'),
        ];
    }
}
