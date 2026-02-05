<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgTurbinePoolSwapInLogResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgTurbinePoolSwapInLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgTurbinePoolSwapInLogs extends ManageRecords
{
    protected static string $resource = MgTurbinePoolSwapInLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
