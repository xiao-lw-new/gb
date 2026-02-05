<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgVipLevelResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgVipLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgVipLevels extends ManageRecords
{
    protected static string $resource = MgVipLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
