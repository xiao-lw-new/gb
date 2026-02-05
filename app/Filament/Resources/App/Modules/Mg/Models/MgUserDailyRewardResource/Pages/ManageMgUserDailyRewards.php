<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgUserDailyRewardResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserDailyRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgUserDailyRewards extends ManageRecords
{
    protected static string $resource = MgUserDailyRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
