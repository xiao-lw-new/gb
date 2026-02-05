<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgSpecifiedVipResource\Pages;
use App\Models\User;
use App\Modules\Mg\Models\MgSpecifiedVip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgSpecifiedVipResource extends BaseResource
{
    protected static ?string $model = MgSpecifiedVip::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = '指定VIP';
    protected static ?string $label = '指定VIP管理';
    protected static ?string $pluralLabel = '指定VIP管理';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('用户地址')
                ->required()
                ->searchable()
                ->unique(ignoreRecord: true)
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

            Forms\Components\Select::make('vip_level')
                ->label('指定VIP等级')
                ->required()
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
                TextColumn::make('user.address')->label('用户地址')->searchable()->copyable(),
                TextColumn::make('vip_level')->label('等级')->sortable()->formatStateUsing(fn ($state) => "V{$state}"),
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
            'index' => Pages\ManageMgSpecifiedVips::route('/'),
        ];
    }
}

