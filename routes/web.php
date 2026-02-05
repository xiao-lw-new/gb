<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MgDashboardReportDownloadController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:admin', 'restrict_domain:admin'])
    ->get('/admin/mg-dashboard-reports/{report}/download', MgDashboardReportDownloadController::class)
    ->name('mg.dashboard-report.download');
