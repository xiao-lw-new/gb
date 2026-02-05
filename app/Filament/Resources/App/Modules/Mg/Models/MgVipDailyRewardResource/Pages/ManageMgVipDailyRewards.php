<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgVipDailyRewardResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgVipDailyRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgVipDailyRewards extends ManageRecords
{
    protected static string $resource = MgVipDailyRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
