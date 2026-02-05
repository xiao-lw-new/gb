<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgUserDailyRewardResource\Pages;
use App\Modules\Mg\Models\MgUserDailyReward;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class MgUserDailyRewardResource extends BaseResource
{
    protected static ?string $model = MgUserDailyReward::class;

    protected static ?string $navigationIcon = 'heroicon-o-sun';
    protected static ?string $navigationGroup = '收益审计';
    protected static ?string $label = '静态收益记录';
    protected static ?string $pluralLabel = '静态收益记录';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.address')
                    ->label('用户地址')
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . $search . '%'))),
                TextColumn::make('date_ref')->label('收益日期')->sortable(),
                TextColumn::make('day_7_reward')->label('7天收益(U)')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('day_15_reward')->label('15天收益(U)')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('expected_amount')->label('预计发放(U)')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('actual_amount')->label('实际发放(U)')->sortable()->color('success')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('remark')->label('备注')->limit(20),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_ref')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('开始日期'),
                        Forms\Components\DatePicker::make('until')->label('结束日期'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('date_ref', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('date_ref', '<=', $date));
                    }),
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
            'index' => Pages\ManageMgUserDailyRewards::route('/'),
        ];
    }

    private static function formatDecimal($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return sprintf('%.4f', (float) $value);
    }
}
