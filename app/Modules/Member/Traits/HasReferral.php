<?php

namespace App\Modules\Member\Traits;

use App\Modules\Member\Models\UserTeamInfo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasReferral
{
    public function teamInfo(): HasOne
    {
        return $this->hasOne(UserTeamInfo::class, 'user_id');
    }

    /**
     * Scope to get all descendants of a specific user using Closure Table.
     * This is highly efficient for deep trees.
     * 
     * @param Builder $query
     * @param int $userId The ID of the ancestor
     * @param bool $includeSelf Whether to include the ancestor itself in the results
     * @return Builder
     */
    public function scopeAllDescendants(Builder $query, int $userId, bool $includeSelf = false): Builder
    {
        // Use Closure Table for maximum efficiency
        $subQuery = DB::table('mg_user_relations')
            ->select('user_id')
            ->where('ancestor_id', $userId);
            
        if (!$includeSelf) {
            $subQuery->where('distance', '>', 0);
        }
        
        return $query->whereIn('id', $subQuery);
    }
    
    /**
     * Get all descendants IDs efficiently using Closure Table.
     * 
     * @param int $userId
     * @param bool $includeSelf
     * @return array
     */
    public static function getDescendantIds(int $userId, bool $includeSelf = false): array
    {
        $query = DB::table('mg_user_relations')
            ->where('ancestor_id', $userId);
            
        if (!$includeSelf) {
            $query->where('distance', '>', 0);
        }
        
        return $query->pluck('user_id')->toArray();
    }
}
