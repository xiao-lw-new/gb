<?php

namespace App\Modules\Member\Http\Controllers;

use App\Models\User;
use App\Modules\Api\Http\Controllers\BaseApiController;
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

        $list = collect($paginator->items())->map(function (User $u) {
            return [
                'address' => $u->address,
                'inviteTime' => $u->created_at?->toDateTimeString(),
            ];
        });

        return $this->success([
            'direct_count' => User::where('p_id', $user->id)->count(),
            'team_count' => $this->getTeamCount((int) $user->id),
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
