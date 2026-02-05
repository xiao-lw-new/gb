<?php

namespace App\Filament\Resources;

use App\Models\Admin;
use Filament\Resources\Resource;

abstract class BaseResource extends Resource
{
    public static function canViewAny(): bool
    {
        $admin = auth('admin')->user();

        if (! $admin) {
            return false;
        }

        $group = static::getNavigationGroup();

        if (! $group) {
            return true;
        }

        if ($admin instanceof Admin) {
            return $admin->canAccessModule($group);
        }

        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }
}
