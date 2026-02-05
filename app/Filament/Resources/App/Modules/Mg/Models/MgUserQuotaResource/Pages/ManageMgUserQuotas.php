<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgUserQuotaResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserQuotaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgUserQuotas extends ManageRecords
{
    protected static string $resource = MgUserQuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
