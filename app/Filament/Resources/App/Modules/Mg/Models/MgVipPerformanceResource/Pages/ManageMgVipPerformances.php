<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgVipPerformanceResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgVipPerformanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgVipPerformances extends ManageRecords
{
    protected static string $resource = MgVipPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
