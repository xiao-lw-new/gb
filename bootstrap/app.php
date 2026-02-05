<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Filament\Facades\Filament;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('system:backup-db')->dailyAt('23:50');

        // Rebuild mg_user_relations from p_id periodically (fix path/p_id drift).
        // Strongest consistency: build into a temp table then atomic swap.
        // NOTE: For boolean options (VALUE_NONE), Symfony does NOT accept `--opt=1`.
        // Use command string flags to avoid scheduler generating `--swap=1` etc.
        $schedule->command('mg:populate-user-relations --source=pid --no-cycle-check --swap')
            ->everyTenMinutes()
            ->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // IMPORTANT:
        // Laravel 12 defaults to redirecting unauthenticated users to route('login'),
        // but this project does not define that route. For API requests, we must
        // return JSON instead of redirecting.
        $middleware->redirectGuestsTo(function ($request) {
            if ($request?->is('api/*')) {
                return null;
            }

            return '/';
        });

        $middleware->alias([
            'restrict_domain' => \App\Http\Middleware\DomainRestriction::class,
            'address_token_login' => \App\Http\Middleware\AddressTokenLogin::class,
        ]);

        // Allow special address token to authenticate BEFORE auth:sanctum on API routes.
        $middleware->prependToGroup('api', \App\Http\Middleware\AddressTokenLogin::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            $routeName = $request->route()?->getName() ?? '';
            if (str_starts_with($routeName, 'filament.admin.')) {
                return redirect()->to(Filament::getUrl());
            }

            return null;
        });

        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if ($e->getStatusCode() !== 403) {
                return null;
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            $routeName = $request->route()?->getName() ?? '';
            if (str_starts_with($routeName, 'filament.admin.')) {
                return redirect()->to(Filament::getUrl());
            }

            return null;
        });

        // 全局处理未认证：返回 200 OK 和空数据
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 4041,
                    'msg'  => 'Unauthenticated',
                    'data' => new \stdClass(),
                ]);
            }
        });

        // 全局异常告警：推送到 Telegram system_warning（过滤常见 4xx）
        $exceptions->reportable(function (\Throwable $e) {
            try {
                // Skip in tests
                if (app()->runningUnitTests()) {
                    return;
                }

                // Skip auth exceptions (very noisy)
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return;
                }

                // Skip common 4xx
                if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                    return;
                }

                app(\App\Services\SystemWarningService::class)->notifyException($e);
            } catch (\Throwable) {
                // Never break exception reporting
            }
        });
    })->create();
