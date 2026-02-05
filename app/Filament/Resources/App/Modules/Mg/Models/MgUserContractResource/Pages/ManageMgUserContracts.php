<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgUserContractResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgUserContracts extends ManageRecords
{
    protected static string $resource = MgUserContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
