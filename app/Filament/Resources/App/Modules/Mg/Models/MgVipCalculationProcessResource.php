<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgVipCalculationProcessResource\Pages;
use App\Modules\Mg\Models\MgVipCalculationProcess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class MgVipCalculationProcessResource extends BaseResource
{
    protected static ?string $model = MgVipCalculationProcess::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = '用户中心';
    protected static ?string $label = 'VIP计算详情';
    protected static ?string $pluralLabel = 'VIP计算详情';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')->disabled(),
                Forms\Components\TextInput::make('calculated_level')->label('计算等级')->disabled(),
                Forms\Components\TextInput::make('total_performance')->label('团队总业绩(U)')->disabled(),
                Forms\Components\TextInput::make('large_area_id')->label('大区ID')->disabled(),
                Forms\Components\TextInput::make('large_area_performance')->label('大区业绩(U)')->disabled(),
                Forms\Components\TextInput::make('small_area_performance')->label('小区业绩(U)')->disabled(),
                Forms\Components\TextInput::make('remark')->label('备注')->disabled(),
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
                TextColumn::make('calculated_level')
                    ->label('计算等级')
                    ->formatStateUsing(fn ($state) => $state === null ? '' : "V{$state}")
                    ->sortable(),
                TextColumn::make('total_performance')->label('团队总业绩(U)')->sortable(),
                TextColumn::make('large_area_id')->label('大区ID')->sortable(),
                TextColumn::make('large_area_performance')->label('大区业绩(U)')->sortable(),
                TextColumn::make('small_area_performance')->label('小区业绩(U)')->sortable(),
                TextColumn::make('remark')->label('备注')->limit(30),
                TextColumn::make('created_at')->label('创建时间')->dateTime(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // 禁用批量操作
            ]);
    }

    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMgVipCalculationProcesses::route('/'),
        ];
    }
}
