<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgUserRewardFlowResource\Pages;
use App\Modules\Mg\Models\MgUserRewardFlow;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class MgUserRewardFlowResource extends BaseResource
{
    protected static ?string $model = MgUserRewardFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = '收益审计';
    protected static ?string $label = '收益流水';
    protected static ?string $pluralLabel = '收益流水';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('user.address')
                    ->label('用户地址')
                    ->sortable()
                    ->searchable(query: fn ($query, $search) => $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . $search . '%'))),
                TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ((string) $state) {
                        'static' => '静态',
                        'referral' => '推荐',
                        'community' => 'VIP',
                        default => (string) $state,
                    })
                    ->color(fn ($state) => match ((string) $state) {
                        'static' => 'info',
                        'referral' => 'warning',
                        'community' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('date_ref')->label('收益日期')->sortable(),
                TextColumn::make('amount')->label('实际发放(U)')->sortable()->color('success')->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('expected_amount')->label('原始应得(U)')->sortable()->formatStateUsing(fn ($state) => self::formatDecimal($state)),
                TextColumn::make('diff')
                    ->label('差额(U)')
                    ->state(function ($record) {
                        $expected = (string) ($record->expected_amount ?? '0');
                        $actual = (string) ($record->amount ?? '0');
                        return bcsub($expected, $actual, 18);
                    })
                    ->formatStateUsing(fn ($state) => self::formatDecimal($state))
                    ->color(fn ($state) => bccomp((string) $state, '0', 18) > 0 ? 'danger' : 'gray'),
                IconColumn::make('need_quota')->label('额度不足')->boolean()->sortable(),
                IconColumn::make('visible')->label('用户可见')->boolean()->sortable(),
                TextColumn::make('created_at')->label('创建时间')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options([
                        'static' => '静态',
                        'referral' => '推荐',
                        'community' => 'VIP',
                    ]),
                Tables\Filters\TernaryFilter::make('need_quota')
                    ->label('仅看额度不足'),
                Tables\Filters\Filter::make('date_ref')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('开始日期'),
                        Forms\Components\DatePicker::make('until')->label('结束日期'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('date_ref', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('date_ref', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ManageMgUserRewardFlows::route('/'),
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

