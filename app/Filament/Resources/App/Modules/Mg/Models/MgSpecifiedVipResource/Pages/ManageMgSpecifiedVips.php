<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgSpecifiedVipResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgSpecifiedVipResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgSpecifiedVips extends ManageRecords
{
    protected static string $resource = MgSpecifiedVipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

