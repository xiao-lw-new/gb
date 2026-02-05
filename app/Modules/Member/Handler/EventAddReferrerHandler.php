<?php

namespace App\Modules\Member\Handler;

use App\Modules\Blockchain\Handlers\EventHandler;
use App\Models\User;
use App\Modules\Member\Helpers\UserHelper;
use Illuminate\Support\Facades\Log;

class EventAddReferrerHandler implements EventHandler
{
    /**
     * 处理推荐人绑定事件
     * 事件原型: EventAddReferrer(uint256 indexed uid, address indexed user, address referrer)
     */
    public function handle(array $event): void
    {
        $data = $event['data'];
        Log::channel('event_add_referrer')->info('[EventAddReferrerHandler]: Processing event data: ' . json_encode($data));

        $userAddr = strtolower($data['user'] ?? '');
        $referrerAddr = strtolower($data['referrer'] ?? '');
        $uid = $data['uid'] ?? 0;

        if (empty($userAddr)) {
            Log::channel('event_add_referrer')->error('[EventAddReferrerHandler]: User address is missing in event data');
            return;
        }

        $contractRootAddr = '0x0000000000000000000000000000000000000001';
        
        // 1. 获取或创建推荐人 (Referrer)
        $referrer = null;
        if ($referrerAddr === $contractRootAddr || empty($referrerAddr) || $referrerAddr === '0x0000000000000000000000000000000000000001') {
            // 如果推荐人为合约根地址或零地址，默认绑定到 ID 1 (系统 Root 用户)
            $referrer = User::find(1);
        } else {
            $referrer = User::where('address', $referrerAddr)->first();
            if (!$referrer) {
                // 如果推荐人尚未在数据库中，先创建一个静默用户 (status=0)
                Log::channel('event_add_referrer')->info("[EventAddReferrerHandler]: Referrer $referrerAddr not found, creating silent user");
                $referrer = UserHelper::createWithReferral($referrerAddr, 1, [
                    'status' => 0, 
                    'remark' => "Auto created as referrer (UID: $uid)"
                ]);
            }
        }

        if (!$referrer) {
            Log::channel('event_add_referrer')->error('[EventAddReferrerHandler]: Failed to resolve referrer for user ' . $userAddr);
            return;
        }

        // 2. 获取或处理当前用户 (User)
        $user = User::where('address', $userAddr)->first();
        if ($user) {
            // NOTE: user-relations / ancestors refresh must NOT rely on $user->path,
            // because modReferer() updates DB in bulk and the in-memory $user object
            // may have stale path/p_id. path is considered deprecated; use p_id recursion.
            $oldAncestors = $this->getAncestorIdsByPid($user->id);

            // 如果用户已存在，且当前父级是 Root (p_id=1)，但链上事件指明了新的非 Root 推荐人
            // 我们允许通过此事件修正其推荐关系（常见于先注册后在链上绑定关系的场景）
            if ($user->p_id == 1 && $referrer->id != 1) {
                Log::channel('event_add_referrer')->info("[EventAddReferrerHandler]: User $userAddr exists under Root, re-binding to $referrerAddr");
                UserHelper::modReferer($user, $referrer);
                // refresh old + new ancestors (p_id recursion)
                $newAncestors = $this->getAncestorIdsByPid($user->id);

                // 如果用户状态是 0（临时用户），现在有了真实推荐人，激活为 1
                if ($user->status == 0) {
                    $user->status = 1;
                    $user->save();
                    Log::channel('event_add_referrer')->info("[EventAddReferrerHandler]: Activated user $userAddr (status 0 -> 1)");
                }
            } else {
                // 即使不满足重绑条件（例如已经是Root且推荐人也是Root，或者已绑定其他推荐人），
                // 如果用户状态为0，说明是临时用户，在此事件中激活
                if ($user->status == 0) {
                    $user->status = 1;
                    $user->save();
                    Log::channel('event_add_referrer')->info("[EventAddReferrerHandler]: User $userAddr exists with p_id " . $user->p_id . ", activated (status 0 -> 1)");
                } else {
                    Log::channel('event_add_referrer')->info("[EventAddReferrerHandler]: User $userAddr already exists with p_id " . $user->p_id . ", skipping");
                }


            }
        } else {
            // 如果用户不存在，直接创建新用户并绑定关系
            Log::channel('event_add_referrer')->info("[EventAddReferrerHandler]: Creating new user $userAddr with referrer $referrerAddr");
            $user = UserHelper::createWithReferral($userAddr, $referrer->id, [
                'remark' => "Created via EventAddReferrer (UID: $uid)"
            ]);
           
        }
    }
    /**
     * Resolve all ancestors by walking p_id upwards (cycle-safe).
     *
     * @return int[] ancestor IDs from direct parent upwards (excludes self)
     */
    protected function getAncestorIdsByPid(int $userId, int $maxDepth = 1000): array
    {
        $ancestors = [];
        $seen = [];
        $curId = $userId;

        for ($i = 0; $i < $maxDepth; $i++) {
            $row = User::query()->select(['id', 'p_id'])->find($curId);
            if (!$row) break;
            $pid = (int) ($row->p_id ?? 0);
            if ($pid <= 0) break;
            if (isset($seen[$pid])) break; // cycle protection

            $seen[$pid] = true;
            $ancestors[] = $pid;
            $curId = $pid;
        }

        return $ancestors;
    }
}
