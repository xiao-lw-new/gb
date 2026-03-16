<?php

namespace App\Modules\Member\Http\Controllers;

use App\Models\User;
use App\Modules\Api\Http\Controllers\BaseApiController;
use App\Modules\Blockchain\Models\UserGoutInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommunityController extends BaseApiController
{
    public function invite(): JsonResponse
    {
        $user = $this->resolveUserFromHeader();
        if (!$user) {
            return $this->success([
                'direct_count' => 0,
                'team_count' => 0,
            ]);
        }

        $directCount = User::where('p_id', $user->id)->count();
        $teamCount = $this->getTeamCount((int) $user->id);

        return $this->success([
            'direct_count' => $directCount,
            'team_count' => $teamCount,
        ]);
    }

    public function inviteList(Request $request): JsonResponse
    {
        $user = $this->resolveUserFromHeader();
        if (!$user) {
            return $this->success([
                'direct_count' => 0,
                'team_count' => 0,
                'list' => [],
                'meta' => [
                    'current_page' => 0,
                    'last_page' => 0,
                    'per_page' => 0,
                    'total' => 0,
                ],
            ]);
        }

        $perPage = (int) $request->input('per_page', 15);
        $query = User::where('p_id', $user->id)->orderBy('created_at', 'desc');
        $paginator = $query->paginate($perPage);

        $userIds = collect($paginator->items())->pluck('id');
        $goutInfoMap = UserGoutInfo::whereIn('user_id', $userIds)->get()->keyBy('user_id');

        $list = collect($paginator->items())->map(function (User $u) use ($goutInfoMap) {
            $gout = $goutInfoMap[$u->id] ?? null;

            return [
                'address' => $u->address,
                'inviteTime' => $u->created_at?->toDateTimeString(),
                'token_balance' => $gout?->token_balance ?? '0',
                'burn_amount_new' => $gout?->burn_amount_new ?? '0',
                'total_burn_amount' => $gout?->total_burn_amount ?? '0',
                'new_gout_amount' => $gout?->new_gout_amount ?? '0',
                'is_qualified' => (int) ($gout?->is_qualified ?? 0),
            ];
        });

        $myGout = UserGoutInfo::where('user_id', $user->id)->first();

        return $this->success([
            'direct_count' => User::where('p_id', $user->id)->count(),
            'team_count' => $this->getTeamCount((int) $user->id),
            'gout_info' => [
                'token_balance' => $myGout?->token_balance ?? '0',
                'burn_amount_new' => $myGout?->burn_amount_new ?? '0',
                'total_burn_amount' => $myGout?->total_burn_amount ?? '0',
                'new_gout_amount' => $myGout?->new_gout_amount ?? '0',
                'is_qualified' => (int) ($myGout?->is_qualified ?? 0),
            ],
            'list' => $list,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    private function getTeamCount(int $userId): int
    {
        if (!Schema::hasTable('user_relations')) {
            return 0;
        }

        return (int) DB::table('user_relations')
            ->where('ancestor_id', $userId)
            ->where('distance', '>', 0)
            ->where('user_id', '!=', $userId)
            ->count();
    }

    private function resolveUserFromHeader(): ?User
    {
        $address = request()->header('Address');
        if (!$address) {
            return null;
        }

        return User::where('address', strtolower($address))->first();
    }
}
