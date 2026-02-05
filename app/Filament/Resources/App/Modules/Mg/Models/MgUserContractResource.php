<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgUserContractResource\Pages;
use App\Modules\Mg\Models\MgUserContract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MgUserContractResource extends BaseResource
{
    protected static ?string $model = MgUserContract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = '质押体系';
    protected static ?string $label = '质押汇总';
    protected static ?string $pluralLabel = '质押汇总';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')->label('用户ID')->sortable()->searchable(),
                TextColumn::make('stakeType')->label('类型')->formatStateUsing(fn ($state) => $state == 1 ? '7天' : '15天'),
                TextColumn::make('u_amount')->label('质押U价值')->sortable(),
                TextColumn::make('amount')->label('代币数量')->sortable(),
                TextColumn::make('updated_at')->label('最后变动')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stakeType')
                    ->options([
                        1 => '7天',
                        2 => '15天',
                    ]),
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
            'index' => Pages\ManageMgUserContracts::route('/'),
        ];
    }
}
