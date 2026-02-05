<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow special login shortcuts for API requests:
 * 1) Header address login (optional): header "address" => existing user
 * 2) Bearer address token login (optional): Authorization: Bearer _token_{address}_{suffix}
 *
 * Security:
 * - MUST be enabled explicitly via config('impersonation.*')
 * - For bearer token login, suffix MUST match config('impersonation.address_token_login_suffix')
 */
class AddressTokenLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $impersonatedUserId = null;

        // If already authenticated, don't override.
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        // 1) Header address login: header "address" => existing user only
        if ((bool) config('impersonation.address_header_token_login_enabled', false)) {
            $addressHeader = (string) ($request->header('address') ?? '');
            $addressHeader = strtolower(trim($addressHeader));
            if ($addressHeader !== '' && preg_match('/^0x[a-f0-9]{40}$/i', $addressHeader)) {
                $user = User::whereRaw('lower(address) = ?', [$addressHeader])->first();
                if ($user) {
                    Auth::guard('web')->setUser($user);
                    Auth::setUser($user);
                    $request->setUserResolver(fn () => $user);
                    $impersonatedUserId = $user->id;
                    try {
                        Log::info("[AddressTokenLogin] header address login user_id={$user->id} address={$addressHeader}");
                    } catch (\Throwable) {
                        // ignore
                    }
                }
            }
        }

        // If header login succeeded, proceed.
        if ($impersonatedUserId) {
            /** @var Response $response */
            $response = $next($request);
            $response->headers->set('X-Impersonated-User', (string) $impersonatedUserId);
            return $response;
        }

        // 2) Bearer token login: Authorization: Bearer _token_{address}_{suffix}
        if ((bool) config('impersonation.address_token_login_enabled', false)) {
            $token = (string) ($request->bearerToken() ?? '');
            if ($token === '' || ! str_starts_with($token, '_token_')) {
                return $next($request);
            }

            $suffix = (string) config('impersonation.address_token_login_suffix', '');
            if ($suffix === '') {
                // Misconfigured: enabled but no suffix secret.
                return $next($request);
            }

            // Expected: _token_{address}_{suffix}
            $prefix = '_token_';
            $rest = substr($token, strlen($prefix));
            $parts = explode('_', $rest);
            if (count($parts) < 2) {
                return $next($request);
            }

            $address = strtolower(array_shift($parts));
            $tokenSuffix = implode('_', $parts);

            // Validate address looks like an EVM address.
            if (! preg_match('/^0x[a-f0-9]{40}$/i', $address)) {
                return $next($request);
            }

            // Constant-time compare
            if (! hash_equals($suffix, $tokenSuffix)) {
                return $next($request);
            }

            // Postgres compare is case-sensitive; match by lower(address).
            $user = User::whereRaw('lower(address) = ?', [$address])->first();
            if (! $user) {
                // Member 模块已移除：仅创建临时用户并绑定 Root(p_id=1)
                try {
                    $user = User::create([
                        'address' => $address,
                        'p_id' => 1,
                        'status' => 0,
                    ]);
                } catch (\Throwable) {
                    return $next($request);
                }
            }

            Auth::guard('web')->setUser($user);
            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);
            $impersonatedUserId = $user->id;

            try {
                Log::info("[AddressTokenLogin] bearer token login user_id={$user->id} address={$address}");
            } catch (\Throwable) {
                // ignore
            }
        }

        /** @var Response $response */
        $response = $next($request);
        if ($impersonatedUserId) {
            $response->headers->set('X-Impersonated-User', (string) $impersonatedUserId);
        }
        return $response;
    }
}

