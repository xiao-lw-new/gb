<?php

namespace App\Modules\Member\Helpers;

use App\Models\User;
use App\Modules\Member\Models\UserRelation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserHelper
{
    /**
     * 创建用户并处理推荐关系
     */
    public static function createWithReferral(string $address, ?int $pid = 1, array $extra = []): User
    {
        $attempts = 0;
        while (true) {
            try {
                return DB::transaction(function () use ($address, $pid, $extra) {
                    $address = strtolower($address);
                    $parent = User::find($pid) ?: User::find(1);
                    
                    $user = new User(array_merge([
                        'address' => $address,
                        'p_id'    => $parent->id,
                        'status'  => 1,
                        'name'    => substr($address, 0, 6) . '...' . substr($address, -4)
                    ], $extra));
                    
                    $user->save();

                    // 修正：路径包含自己的 ID
                    $user->path = $parent->path . $user->id . '|';
                    $user->save();

                    // 维护闭包表关系
                    self::rebuildUserRelations($user);

                    return $user;
                });
            } catch (\Illuminate\Database\QueryException $e) {
                $attempts++;
                if ($attempts > 1 || $e->getCode() !== '23505' || !str_contains($e->getMessage(), 'users_pkey')) {
                    throw $e;
                }

                // Fix sequence drift after manual imports.
                self::resetUsersIdSequence();
            }
        }
    }

    /**
     * 修改推荐人 (核心难点：递归更新路径和团队信息)
     */
    public static function modReferer(User $user, User $newParent): void
    {
        DB::transaction(function () use ($user, $newParent) {
            $oldPath = $user->path;
            $newPath = $newParent->path . $newParent->id . '|';
            $oldAncestors = array_filter(explode('|', trim($oldPath, '|')));

            // 1. 更新当前用户及其所有子孙的 path
            // 使用 PostgreSQL 的 replace 函数优化
            DB::statement("UPDATE users SET 
                path = REPLACE(path, ?, ?),
                p_id = CASE WHEN id = ? THEN ? ELSE p_id END
                WHERE path LIKE ? OR id = ?", [
                $oldPath . $user->id . '|', 
                $newPath . $user->id . '|',
                $user->id, $newParent->id,
                $oldPath . $user->id . '|%',
                $user->id
            ]);

            // 2. 刷新闭包表 (user_relations)
            // 找出所有受影响的用户（当前用户 + 所有下级）
            // 注意：此时数据库中的 path 已经更新为 newPath
            $affectedPathPrefix = $newPath . $user->id . '|';
            
            // 先处理当前用户
            self::rebuildUserRelations($user);
            
            // 处理所有下级
            User::where('path', 'like', $affectedPathPrefix . '%')->chunk(1000, function ($users) {
                foreach ($users as $subUser) {
                    self::rebuildUserRelations($subUser);
                }
            });

        });
    }

    /**
     * 重建单个用户的关系数据
     */
    public static function rebuildUserRelations($user): void
    {
        if (!$user instanceof User) {
            $user = User::find($user->id ?? null);
        }
        if (!$user) {
            return;
        }
        // 1. 删除旧关系
        UserRelation::where('user_id', $user->id)->delete();

        // 2. 解析 Path 构建新关系
        $path = trim($user->path, '|');
        if (empty($path)) return;

        $ids = explode('|', $path);
        $reversedIds = array_reverse($ids);
        
        $insertData = [];
        foreach ($reversedIds as $index => $ancestorId) {
            $ancestorId = (int)$ancestorId;
            if ($ancestorId === 0) continue;
            
            $insertData[] = [
                'user_id' => $user->id,
                'ancestor_id' => $ancestorId,
                'distance' => $index,
            ];
        }

        if (!empty($insertData)) {
            // Use insertOrIgnore to prevent race conditions or duplicates
            UserRelation::insertOrIgnore($insertData);
        }
    }

    protected static function resetUsersIdSequence(): void
    {
        DB::statement(
            "SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT COALESCE(MAX(id), 1) FROM users), true)"
        );
    }
}
