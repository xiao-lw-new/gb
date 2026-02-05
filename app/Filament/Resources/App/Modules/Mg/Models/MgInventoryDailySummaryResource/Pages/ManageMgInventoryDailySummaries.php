<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgInventoryDailySummaryResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgInventoryDailySummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgInventoryDailySummaries extends ManageRecords
{
    protected static string $resource = MgInventoryDailySummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

