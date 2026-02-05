<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgTurbinePoolSwapOutLogResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgTurbinePoolSwapOutLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgTurbinePoolSwapOutLogs extends ManageRecords
{
    protected static string $resource = MgTurbinePoolSwapOutLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
