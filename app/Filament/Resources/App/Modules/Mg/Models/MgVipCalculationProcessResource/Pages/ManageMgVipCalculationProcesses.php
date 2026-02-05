<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgVipCalculationProcessResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgVipCalculationProcessResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMgVipCalculationProcesses extends ManageRecords
{
    protected static string $resource = MgVipCalculationProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
