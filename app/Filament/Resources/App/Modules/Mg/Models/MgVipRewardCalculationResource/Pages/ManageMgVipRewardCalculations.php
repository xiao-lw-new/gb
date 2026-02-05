<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgVipRewardCalculationResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgVipRewardCalculationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgVipRewardCalculations extends ManageRecords
{
    protected static string $resource = MgVipRewardCalculationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
