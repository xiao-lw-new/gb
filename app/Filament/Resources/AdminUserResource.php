<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages;
use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class AdminUserResource extends BaseResource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = '系统管理';
    protected static ?string $label = '次级管理员';
    protected static ?string $pluralLabel = '次级管理员';

    public static function canViewAny(): bool
    {
        $admin = auth('admin')->user();

        return $admin instanceof Admin && $admin->isSuper();
    }

    public static function canCreate(): bool
    {
        $admin = auth('admin')->user();

        return $admin instanceof Admin && $admin->isSuper();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->label('用户名')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'admin_users', column: 'username', ignorable: fn ($record) => $record),

                Forms\Components\TextInput::make('name')
                    ->label('姓名')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->maxLength(255)
                    ->unique(table: 'admin_users', column: 'email', ignorable: fn ($record) => $record),

                Forms\Components\TextInput::make('password')
                    ->label('密码')
                    ->password()
                    ->required(fn ($record) => $record === null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->helperText('留空则不修改密码。'),

                Forms\Components\CheckboxList::make('modules')
                    ->label('模块权限')
                    ->options(Admin::moduleOptions())
                    ->columns(2)
                    ->default([])
                    ->helperText('仅对二级管理员生效。'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('username')->label('用户名')->searchable(),
                TextColumn::make('name')->label('姓名')->searchable(),
                TextColumn::make('email')->label('邮箱')->searchable(),
                TextColumn::make('modules')
                    ->label('模块权限')
                    ->formatStateUsing(function ($state, $record) {
                        if (! is_array($state)) {
                            return '';
                        }

                        return implode(', ', $state);
                    })
                    ->limit(50),
                TextColumn::make('created_at')->label('创建时间')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record instanceof Admin && ! $record->isSuper()),
            ])
            ->bulkActions([
                // 禁用批量操作
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAdminUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_super', false);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_super'] = false;

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['is_super'] = false;

        return $data;
    }
}
