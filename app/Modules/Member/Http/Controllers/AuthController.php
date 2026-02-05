<?php

namespace App\Modules\Member\Http\Controllers;

use App\Models\User;
use App\Modules\Api\Http\Controllers\BaseApiController;
use App\Modules\Member\Services\AddressSignService;
use App\Modules\Member\Helpers\UserHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseApiController
{
    protected AddressSignService $signService;

    public function __construct(AddressSignService $signService)
    {
        $this->signService = $signService;
    }

    /**
     * 获取登录 Nonce (随机码)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function nonce(Request $request): JsonResponse
    {
        $request->validate([
            'address' => 'required|string|size:42',
        ]);

        $address = strtolower($request->input('address'));

        try {
            $data = $this->signService->getSignCode($address);
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error('Failed to get sign code: ' . $e->getMessage());
        }
    }

    /**
     * Web3 签名登录
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'address' => 'required|string|size:42',
            'signature' => 'required|string',
        ]);

        $address = strtolower($request->input('address'));
        $signature = $request->input('signature');

        // 1. 验证签名
        $isValid = $this->signService->verifySign($address, $signature);

        if (!$isValid) {
            // 开发模式下允许特定地址绕过验证
            if (config('app.debug') && str_starts_with($address, '0x0000')) {
                $isValid = true;
            } else {
                return $this->error('Invalid signature or nonce expired', 401);
            }
        }

        // 2. 查找用户
        // 注册是通过区块链扫块处理的，但如果未扫到，允许创建临时用户（status=0, 绑定到Root）
        $user = User::where('address', $address)->first();

        if (!$user) {
            Log::channel('user_auth')->info("User not found, creating temporary status=0 user bound to Root", ['address' => $address]);
            // 创建 status=0 的用户，绑定到 ID 1 (Root)
            $user = UserHelper::createWithReferral($address, 1, ['status' => 0]);
        }

        if ($user->status == 2) {
            return $this->error('Account is disabled', 403);
        }

        // 3. 登录成功
        // 注意：不再自动将 status=0 变为 1。只有当用户绑定了推荐人后（EventAddReferrerHandler）才变为 1。


        // 4. 颁发 Token (Sanctum)
        $expiration = config('sanctum.expiration');
        $expiresAt = $expiration ? now()->addMinutes($expiration) : null;
        
        $tokenInstance = $user->createToken('web3-login', ['*'], $expiresAt);
        $token = $tokenInstance->plainTextToken;

        Log::channel('user_auth')->info("User logged in successfully", [
            'id' => $user->id,
            'address' => $user->address
        ]);

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'address' => $user->address,
                'name' => $user->name,
                'status' => $user->status,
                'active' => $user->active,
            ]
        ], 'Login successful');
    }

    /**
     * 退出登录
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }

    /**
     * 获取当前用户信息
     */
    public function userInfo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->success([
                'id'      => 0,
                'address' => '',
                'name'    => 'Guest',
                'status'  => 0,
                'active'  => 0,
            ]);
        }
        return $this->success($user);
    }
}
