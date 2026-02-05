<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgVipPerformanceResource\Pages;
use App\Modules\Mg\Models\MgVipPerformance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class MgVipPerformanceResource extends BaseResource
{
    protected static ?string $model = MgVipPerformance::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = '用户中心';
    protected static ?string $label = 'VIP业绩看板';
    protected static ?string $pluralLabel = 'VIP业绩看板';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')->disabled(),
                Forms\Components\TextInput::make('personal_performance')->numeric(),
                Forms\Components\TextInput::make('total_performance')->numeric(),
                Forms\Components\TextInput::make('large_area_id')->numeric(),
                Forms\Components\TextInput::make('large_area_performance')->numeric(),
                Forms\Components\TextInput::make('small_area_performance')->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.address')
                    ->label('用户地址')
                    ->sortable()
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHas(
                        'user',
                        fn (Builder $subQuery) => $subQuery->where('address', 'like', '%' . strtolower(trim($search)) . '%')
                    )),
                TextColumn::make('personal_performance')->label('个人业绩(U)')->sortable(),
                TextColumn::make('total_performance')->label('团队总业绩(U)')->sortable(),
                TextColumn::make('large_area_id')->label('大区ID')->sortable(),
                TextColumn::make('large_area_performance')->label('大区业绩(U)')->sortable(),
                TextColumn::make('small_area_performance')->label('小区业绩(U)')->sortable(),
                TextColumn::make('direct_active_count')->label('直推激活')->sortable(),
                TextColumn::make('team_active_count')->label('团队激活')->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ManageMgVipPerformances::route('/'),
        ];
    }
}
