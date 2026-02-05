<?php

namespace App\Http\Controllers;

use App\Modules\Mg\Models\MgDashboardReport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class MgDashboardReportDownloadController extends Controller
{
    public function __invoke(int $report): StreamedResponse
    {
        $record = MgDashboardReport::query()->findOrFail($report);
        if (!$record->file_path || !Storage::disk('public')->exists($record->file_path)) {
            abort(404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $filename = basename($record->file_path);
        return $disk->download($record->file_path, $filename);
    }
}
