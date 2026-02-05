<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgUserRewardFlowResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgUserRewardFlowResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMgUserRewardFlows extends ManageRecords
{
    protected static string $resource = MgUserRewardFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // read-only
        ];
    }
}

