<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class UserResource extends BaseResource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = '用户中心';
    protected static ?string $label = '用户管理';
    protected static ?string $pluralLabel = '用户管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('address')
                    ->label('钱包地址')
                    ->disabled(),
                Forms\Components\TextInput::make('p_id')
                    ->label('上级ID')
                    ->numeric(),
                Forms\Components\Toggle::make('active')
                    ->label('激活状态'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('address')
                    ->label('钱包地址')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('p_id')
                    ->label('上级ID')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('激活状态')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('仅查看激活用户'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // 禁用批量操作
            ]);
    }

    public static function canEdit($record): bool { return false; } public static function canDelete($record): bool { return false; } public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
