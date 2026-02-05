<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MgPerformanceTotalsWidget;
use App\Filament\Widgets\MgInventorySummaryWidget;
use App\Filament\Widgets\MgInventoryDailyLatestUsersWidget;
use App\Filament\Widgets\MgInventoryDailySummaryRecentWidget;
use App\Filament\Widgets\MgRewardFlowWidget;
use App\Filament\Widgets\MgStakeFlowWidget;
use App\Filament\Widgets\MgUserStatsWidget;
use App\Modules\Mg\Models\MgDashboardReport;
use App\Modules\Mg\Services\MgDashboardReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = '仪表盘';

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('start_date')
                ->label('开始日期')
                ->default(fn () => now()->subDays(6)->toDateString())
                ->closeOnDateSelection(),
            DatePicker::make('end_date')
                ->label('结束日期')
                ->default(fn () => now()->toDateString())
                ->closeOnDateSelection(),
            Select::make('channel')
                ->label('渠道')
                ->options([
                    'all' => '全部',
                    'robot' => '机器人地址',
                    'normal' => '正常地址',
                ])
                ->default('all'),
            TextInput::make('user_id')
                ->label('用户ID')
                ->numeric()
                ->placeholder('例如: 1569'),
            TextInput::make('address')
                ->label('用户地址')
                ->placeholder('0x...'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateReport')
                ->label('生成报表')
                ->action(function (MgDashboardReportService $service): void {
                    $service->generateFromFilters($this->filters ?? []);
                    Notification::make()
                        ->title('报表已生成')
                        ->success()
                        ->send();
                }),
            Action::make('downloadLatest')
                ->label('下载最新报表')
                ->url(fn (): ?string => $this->getLatestReportDownloadUrl())
                ->openUrlInNewTab()
                ->visible(function (): bool {
                    return (bool) $this->getLatestReportDownloadUrl();
                }),
        ];
    }

    public function getWidgets(): array
    {
        return [
            MgUserStatsWidget::class,
            MgPerformanceTotalsWidget::class,
            MgInventorySummaryWidget::class,
            MgStakeFlowWidget::class,
            MgRewardFlowWidget::class,
            MgInventoryDailyLatestUsersWidget::class,
            MgInventoryDailySummaryRecentWidget::class,
        ];
    }

    protected function getLatestReportDownloadUrl(): ?string
    {
        $report = $this->getLatestReportForFilters();
        if (!$report || !$report->file_path) {
            return null;
        }

        if (!Storage::disk('public')->exists($report->file_path)) {
            return null;
        }

        return route('mg.dashboard-report.download', ['report' => $report->id]);
    }

    protected function getLatestReportForFilters(): ?MgDashboardReport
    {
        $filters = $this->filters ?? [];
        $query = MgDashboardReport::query()->orderByDesc('id');

        if (!empty($filters['start_date'])) {
            $query->whereDate('date_start', '>=', Carbon::parse($filters['start_date'])->toDateString());
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('date_end', '<=', Carbon::parse($filters['end_date'])->toDateString());
        }
        if (!empty($filters['channel'])) {
            $query->where('filters->channel', $filters['channel']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('filters->user_id', (int) $filters['user_id']);
        }
        if (!empty($filters['address'])) {
            $query->where('filters->address', strtolower(trim((string) $filters['address'])));
        }

        return $query->first();
    }
}
