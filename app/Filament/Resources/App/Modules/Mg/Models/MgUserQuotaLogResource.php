<?php

namespace App\Filament\Resources\App\Modules\Mg\Models;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\App\Modules\Mg\Models\MgUserQuotaLogResource\Pages;
use App\Modules\Mg\Models\MgUserQuotaLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class MgUserQuotaLogResource extends BaseResource
{
    protected static ?string $model = MgUserQuotaLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = '收益配额';
    protected static ?string $label = '配额流水';
    protected static ?string $pluralLabel = '配额流水';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.address')
                    ->label('用户地址')
                    ->copyable()
                    ->sortable()
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . strtolower(trim($search)) . '%'));
                    }),
                TextColumn::make('type')->label('类型')->formatStateUsing(fn ($state) => $state == 0 ? '购买' : '收益扣除'),
                TextColumn::make('quota_before')->label('变动前')->numeric(4),
                TextColumn::make('amount')->label('变动额')->numeric(4)->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('quota_after')->label('变动后')->numeric(4),
                TextColumn::make('remark')->label('备注')->limit(20),
                TextColumn::make('created_at')->label('时间')->dateTime('Y-m-d H:i:s')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        0 => '购买',
                        1 => '扣除',
                    ]),
                Tables\Filters\Filter::make('user_address')
                    ->form([
                        TextInput::make('address')->label('用户地址'),
                    ])
                    ->query(function ($query, array $data) {
                        $addr = strtolower(trim((string) ($data['address'] ?? '')));
                        if ($addr === '') {
                            return;
                        }
                        $query->whereHas('user', fn ($q) => $q->where('address', 'like', '%' . $addr . '%'));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ManageMgUserQuotaLogs::route('/'),
        ];
    }
}
