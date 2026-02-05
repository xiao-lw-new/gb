<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgReferralRewardCalculationResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgReferralRewardCalculationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgReferralRewardCalculations extends ManageRecords
{
    protected static string $resource = MgReferralRewardCalculationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
