<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DomainRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $type  'user' or 'admin'
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $allowedDomain = config("domains.$type");
        $currentHost = $request->getHost();

        // Support single domain string or comma-separated domains in env/config.
        $allowedDomains = [];
        if (is_array($allowedDomain)) {
            $allowedDomains = $allowedDomain;
        } elseif (is_string($allowedDomain) && $allowedDomain !== '') {
            $allowedDomains = array_map('trim', explode(',', $allowedDomain));
        }
        $allowedDomains = array_values(array_filter($allowedDomains, fn ($v) => is_string($v) && $v !== ''));

        // If config is empty, treat as no restriction.
        $isAllowed = empty($allowedDomains) || in_array($currentHost, $allowedDomains, true);

        if (! $isAllowed && app()->environment('production')) {
            abort(403, 'Unauthorized domain access.');
        }

        return $next($request);
    }
}
