<?php

namespace App\Filament\Resources\App\Modules\Mg\Models\MgCommunityLeaderResource\Pages;

use App\Filament\Resources\App\Modules\Mg\Models\MgCommunityLeaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMgCommunityLeaders extends ManageRecords
{
    protected static string $resource = MgCommunityLeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

