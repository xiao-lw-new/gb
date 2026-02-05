<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgUserContractLogResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserContractLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgUserContractLogs extends ManageRecords
{
    protected static string $resource = MgUserContractLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
