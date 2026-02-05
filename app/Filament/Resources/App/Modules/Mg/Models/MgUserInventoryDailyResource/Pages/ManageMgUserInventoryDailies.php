<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgUserInventoryDailyResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserInventoryDailyResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgUserInventoryDailies extends ManageRecords
{
    protected static string $resource = MgUserInventoryDailyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

