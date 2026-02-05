<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'blockchain' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/block.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'mg_stake' => [
            'driver' => 'daily',
            'path' => storage_path('logs/mg/stake.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'chain_event' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'chain_event_decode' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_decode.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'chain_event_replenish' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_replenish.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'transaction_queue' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/transaction_queue.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'data_verify' => [
            'driver' => 'daily',
            'path' => storage_path('logs/verify/error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'chain_service' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/service.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],


        'daily_energy' => [
            'driver' => 'daily',
            'path' => storage_path('logs/task/daily_energy.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'partner_calc' => [
            'driver' => 'daily',
            'path' => storage_path('logs/task/partner_calc.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],



        'chain_method' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/chain_method.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'claim_reward' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/claim_reward.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],


        'check_data' => [
            'driver' => 'daily',
            'path' => storage_path('logs/csm/check_data.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'new_reward' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/new_reward.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'merkle_tree' => [
            'driver' => 'daily',
            'path' => storage_path('logs/csm/merkle_tree.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'share_reward' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/share_reward.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'team_reward' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/team_reward.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'same_level' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/same_level.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'address_info' => [
            'driver' => 'daily',
            'path' => storage_path('logs/csm/address_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'address_level' => [
            'driver' => 'daily',
            'path' => storage_path('logs/csm/address_level.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'data_inventory' => [
            'driver' => 'daily',
            'path' => storage_path('logs/csm/data_inventory.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'daily_new' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/daily_new.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'rank_daily_new_reward' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/rank_daily_new_reward.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'address_referrer' => [
            'driver' => 'daily',
            'path' => storage_path('logs/csm/address_referrer.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'studio_lp_change' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/studio_lp_change.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'user_opt' => [
            'driver' => 'daily',
            'path' => storage_path('logs/user/opt.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'tg_bot' => [
            'driver' => 'daily',
            'path' => storage_path('logs/telegram/bot.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'job' => [
            'driver' => 'daily',
            'path' => storage_path('logs/system/job.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'refresh_contribution_data' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/refresh_contribution_data.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'dynamic_overage' => [
            'driver' => 'daily',
            'path' => storage_path('logs/rewards/dynamic_overage.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],


        'event_receive' => [
            'driver' => 'daily',
            'path' => storage_path('logs/fund/event_receive.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'code_sign' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth/code_sign.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],

        'user_auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth/user_auth.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],

        'mg_performance' => [
            'driver' => 'daily',
            // 统一输出到 storage/logs/mg/performance-YYYY-MM-DD.log，方便运维 tail
            'path' => storage_path('logs/mg/performance.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'event_add_referrer' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_add_referrer.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'event_buy_profit_quota' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_buy_profit_quota.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'event_stake' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_stake.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'event_unstake' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_unstake.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'event_turbine_in' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_turbine_in.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'test_address' => [
            'driver' => 'daily',
            'path' => storage_path('logs/test/address.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'event_turbine_out' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_turbine_out.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],

        'event_release_pool' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_release_pool.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],
        'event_tax_processor' => [
            'driver' => 'daily',
            'path' => storage_path('logs/chain/event_tax_processor.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 5,
        ],
    ],
];
