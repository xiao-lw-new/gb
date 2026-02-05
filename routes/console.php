<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('mg:check-active-users')->everyTenMinutes();
Schedule::command('mg:refresh-active-counts')->everyThirtyMinutes();

// 业绩刷新：1小时1次
Schedule::command('mg:calculate-vip-performance')->everyTenMinutes();

// VIP等级计算：2小时1次
Schedule::command('mg:calculate-vip-level')->everyTenMinutes();
