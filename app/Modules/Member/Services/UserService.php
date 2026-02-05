<?php

namespace App\Modules\Member\Services;

use App\Models\User;
use App\Modules\Member\Models\UserRelation;

class UserService
{
    /**
     * 增量更新上级的激活计数
     */
    protected function updateAncestorsActiveCounts(User $user, int $increment): void
    {
        // Only default count fields here; performance fields are maintained by MgPerformanceService.
        // (They have DB defaults anyway, but keeping this minimal avoids accidental overwrites in the future.)
        $defaults = [
            'direct_active_count' => 0,
            'team_active_count' => 0,
            'direct_count' => 0,
            'team_count' => 0,
        ];


        // 更新所有上级的团队激活数（不使用 users.path，使用闭包表 mg_user_relations）
        $ancestorIds = UserRelation::query()
            ->where('user_id', $user->id)
            ->where('distance', '>', 0) // exclude self(distance=0)
            ->pluck('ancestor_id')
            ->all();


    }

}
