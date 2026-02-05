<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgReferralDailyRewardResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgReferralDailyRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgReferralDailyRewards extends ManageRecords
{
    protected static string $resource = MgReferralDailyRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
