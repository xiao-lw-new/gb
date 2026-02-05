<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgCommunityLeaderResource\Pages;
use App\Models\User;
use App\Modules\Mg\Models\MgCommunityLeader;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgCommunityLeaderResource extends BaseResource
{
    protected static ?string $model = MgCommunityLeader::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = '社区管理';
    protected static ?string $label = '社区领导人';
    protected static ?string $pluralLabel = '社区领导人';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('领导人用户')
                ->required()
                ->searchable()
                ->getSearchResultsUsing(function (string $search): array {
                    $search = trim($search);
                    if ($search === '') return [];

                    return User::query()
                        ->where('address', 'like', '%' . strtolower($search) . '%')
                        ->orderByDesc('id')
                        ->limit(50)
                        ->pluck('address', 'id')
                        ->toArray();
                })
                ->getOptionLabelUsing(function ($value): string {
                    if (!$value) return '';
                    $u = User::find($value);
                    return $u?->address ?? (string) $value;
                })
                ->helperText('支持按钱包地址搜索用户'),

            Forms\Components\TextInput::make('name')
                ->label('社区名称')
                ->maxLength(255),

            Forms\Components\TextInput::make('number')
                ->label('编号')
                ->maxLength(255)
                ->helperText('不做唯一校验，可为空'),

            Forms\Components\Textarea::make('remark')
                ->label('备注')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user_id')->label('用户ID')->sortable()->searchable(),
                TextColumn::make('user.address')->label('领导人地址')->searchable()->copyable(),
                TextColumn::make('name')->label('社区名称')->searchable(),
                TextColumn::make('number')->label('编号')->searchable(),
                TextColumn::make('remark')->label('备注')->limit(30),
                TextColumn::make('updated_at')->label('更新时间')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMgCommunityLeaders::route('/'),
        ];
    }
}

