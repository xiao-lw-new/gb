<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgUserQuotaLogResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserQuotaLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgUserQuotaLogs extends ManageRecords
{
    protected static string $resource = MgUserQuotaLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
