<?php

namespace App\Filament\Resources\Reportings\Pages;

use App\Models\Investigation;
use App\Models\InOut;
use App\Models\MonthlyReportSummary;
use App\Models\Consultant;
use App\Filament\Resources\Reportings\ReportingResource;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportingReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ReportingResource::class;

    protected string $view = 'filament.resources.reportings.pages.reporting-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->resolveFormData(request()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Grid::make(4)
                    ->schema([
                        Select::make('report_type')
                            ->label('Report Type')
                            ->options([
                                'monthly' => 'Monthly Report',
                                'monthly_friday' => 'Monthly Report Friday',
                                'monthly_summary' => 'Monthly Report Summery',
                                'presence_daily_weekly' => 'Consultant Presence Daily & Weekly',
                                'late_in_early_out' => 'Late In & Early Out',
                                'yearly' => 'Yearly Report',
                                'consultant' => 'Consultant Wise Report',
                            ])
                            ->default('monthly')
                            ->live()
                            ->required(),
                        Select::make('month')
                            ->options(fn (Get $get): array => $this->getMonthOptions((int) ($get('year') ?? now()->format('Y')), $get('report_type')))
                            ->placeholder('Select available month')
                            ->visible(fn (Get $get): bool => in_array($get('report_type'), ['monthly', 'monthly_friday', 'monthly_summary', 'presence_daily_weekly', 'late_in_early_out'], true))
                            ->required(fn (Get $get): bool => in_array($get('report_type'), ['monthly', 'monthly_friday', 'monthly_summary', 'presence_daily_weekly', 'late_in_early_out'], true)),
                        Select::make('year')
                            ->options(fn (Get $get): array => $this->getYearOptions($get('report_type')))
                            ->placeholder('Select available year')
                            ->default(fn (): ?int => isset($this->data['year']) ? (int) $this->data['year'] : null)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                $monthOptions = $this->getMonthOptions((int) $state, $get('report_type'));
                                $selectedMonth = (int) ($get('month') ?? 0);

                                if ($monthOptions === []) {
                                    $set('month', null);

                                    return;
                                }

                                if (! array_key_exists($selectedMonth, $monthOptions)) {
                                    $set('month', array_key_first($monthOptions));
                                }
                            })
                            ->required(fn (Get $get): bool => in_array($get('report_type'), ['monthly', 'monthly_friday', 'monthly_summary', 'presence_daily_weekly', 'late_in_early_out', 'yearly'], true)),
                        Select::make('consultant_id')
                            ->label('Consultant')
                            ->options(fn (): array => ReportingResource::getAccessibleConsultantOptions())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get): bool => $get('report_type') === 'consultant')
                            ->required(fn (Get $get): bool => $get('report_type') === 'consultant'),
                        Select::make('time_frame')
                            ->label('Time Frame')
                            ->options($this->getTimeFrameOptions())
                            ->placeholder('All time frames')
                            ->live(),
                    ]),
            ]);
    }

    public function getTitle(): string
    {
        return 'Reporting Reports';
    }

    public function getHeading(): string
    {
        return 'Reporting Reports';
    }

    public function getSubheading(): ?string
    {
        return 'View monthly, yearly, and consultant-wise reporting summaries.';
    }

    public function showsFilters(): bool
    {
        return true;
    }

    public function usesBrowserPrint(): bool
    {
        return false;
    }

    public function getPrintUrl(): string
    {
        return route('reportings.print.preview', $this->getPrintQueryParameters());
    }

    public function getReportRows(): Collection
    {
        $filters = $this->data ?? [];
        $reportType = $filters['report_type'] ?? 'monthly';
        $selectedMonth = (int) ($filters['month'] ?? now()->format('n'));
        $selectedYear = (int) ($filters['year'] ?? now()->format('Y'));
        $timeFrame = $filters['time_frame'] ?? null;
        $daysInMonth = match ($reportType) {
            'monthly' => Carbon::createFromDate($selectedYear, $selectedMonth, 1)->daysInMonth,
            'monthly_friday' => $this->countFridaysInMonth($selectedYear, $selectedMonth),
            default => 0,
        };

        if ($reportType === 'consultant') {
            $consultantId = $filters['consultant_id'] ?? null;

            if (blank($consultantId)) {
                return collect();
            }

            $query = ReportingResource::getEloquentQuery()
                ->leftJoin('consultants', 'reportings.consultant_id', '=', 'consultants.id')
                ->leftJoin('departments', 'reportings.department_id', '=', 'departments.id')
                ->leftJoin('remarks', 'reportings.remark_id', '=', 'remarks.id')
                ->where('reportings.consultant_id', $consultantId)
                ->orderBy('reportings.date')
                ->select([
                    'reportings.date',
                    'consultants.name as consultant_name',
                    'departments.name as department_name',
                    'reportings.new',
                    'reportings.report',
                    'reportings.follow_up',
                    'reportings.back',
                    'reportings.total',
                    'remarks.name as remark_name',
                ]);

            $this->applyTimeFrameFilter($query, $timeFrame);

            return $query
                ->get()
                ->map(fn ($row): array => [
                    'date' => $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
                    'consultant_name' => $row->consultant_name ?? '-',
                    'department_name' => $row->department_name ?? '-',
                    'new' => (int) $row->new,
                    'report' => (int) $row->report,
                    'follow_up' => (int) $row->follow_up,
                    'back' => (int) $row->back,
                    'total' => (int) $row->total,
                    'remark' => $row->remark_name ?? '-',
                    'off_day' => null,
                    'leave' => null,
                    'absent' => null,
                    'chamber' => null,
                ]);
        }

        if ($reportType === 'late_in_early_out') {
            return collect();
        }

        $query = ReportingResource::getEloquentQuery()
            ->leftJoin('consultants', 'reportings.consultant_id', '=', 'consultants.id')
            ->leftJoin('departments', 'reportings.department_id', '=', 'departments.id')
            ->leftJoin('remarks', 'reportings.remark_id', '=', 'remarks.id')
            ->selectRaw('
                reportings.consultant_id,
                consultants.name as consultant_name,
                departments.name as department_name,
                COALESCE(SUM(reportings.new), 0) as new_total,
                COALESCE(SUM(reportings.report), 0) as report_total,
                COALESCE(SUM(reportings.follow_up), 0) as follow_up_total,
                COALESCE(SUM(reportings.back), 0) as back_total,
                COALESCE(SUM(reportings.total), 0) as total_total,
                COUNT(DISTINCT CASE
                    WHEN LOWER(REPLACE(remarks.name, " ", "")) IN ("offday", "off-day")
                    THEN reportings.date
                END) as off_day_total,
                COUNT(DISTINCT CASE
                    WHEN LOWER(remarks.name) = "leave"
                    THEN reportings.date
                END) as leave_total,
                COUNT(DISTINCT CASE
                    WHEN LOWER(remarks.name) = "absent"
                    THEN reportings.date
                END) as absent_total
            ')
            ->groupBy('reportings.consultant_id', 'consultants.name', 'departments.name')
            ->orderBy('consultants.name');

        if (in_array($reportType, ['monthly', 'monthly_friday'], true)) {
            $query
                ->whereYear('reportings.date', $selectedYear)
                ->whereMonth('reportings.date', $selectedMonth);

            if ($reportType === 'monthly_friday') {
                $query->whereRaw('DAYOFWEEK(reportings.date) = 6');
            }
        }

        if ($reportType === 'yearly') {
            $query->whereYear('reportings.date', $selectedYear);
        }

        $this->applyTimeFrameFilter($query, $timeFrame);

        return $query
            ->get()
            ->map(function ($row) use ($reportType, $daysInMonth, $selectedMonth, $selectedYear): array {
                $offDay = (int) $row->off_day_total;
                $leave = (int) $row->leave_total;
                $absent = (int) $row->absent_total;
                $report = (int) $row->report_total;
                $followUp = (int) $row->follow_up_total;
                $totalOldPt = $report + $followUp;
                $investigationId = 0.0;
                $investigationAmount = 0.0;

                if (in_array($reportType, ['monthly', 'monthly_friday'], true)) {
                    $investigation = Investigation::query()
                        ->where('consultant_id', $row->consultant_id)
                        ->whereYear('month', $selectedYear)
                        ->whereMonth('month', $selectedMonth)
                        ->selectRaw('
                            COALESCE(SUM(CAST(investigation_id AS DECIMAL(12, 2))), 0) as investigation_id_total,
                            COALESCE(SUM(amount), 0) as amount_total
                        ')
                        ->first();

                    $investigationId = (float) ($investigation?->investigation_id_total ?? 0);
                    $investigationAmount = (float) ($investigation?->amount_total ?? 0);
                }

                return [
                    'consultant_name' => $row->consultant_name ?? '-',
                    'department_name' => $row->department_name ?? '-',
                    'new' => (int) $row->new_total,
                    'report' => $report,
                    'follow_up' => $followUp,
                    'back' => (int) $row->back_total,
                    'total' => (int) $row->total_total,
                    'off_day' => in_array($reportType, ['monthly', 'monthly_friday'], true) ? $offDay : null,
                    'leave' => in_array($reportType, ['monthly', 'monthly_friday'], true) ? $leave : null,
                    'absent' => in_array($reportType, ['monthly', 'monthly_friday'], true) ? $absent : null,
                    'total_old_pt' => in_array($reportType, ['monthly', 'monthly_friday'], true) ? $totalOldPt : null,
                    'investigation_id' => in_array($reportType, ['monthly', 'monthly_friday'], true) ? $investigationId : null,
                    'investigation_percentage' => in_array($reportType, ['monthly', 'monthly_friday'], true) && $totalOldPt > 0
                        ? round(($investigationId * 100) / $totalOldPt, 2)
                        : 0,
                    'investigation_amount' => in_array($reportType, ['monthly', 'monthly_friday'], true) ? $investigationAmount : null,
                    'chamber' => in_array($reportType, ['monthly', 'monthly_friday'], true)
                        ? max($daysInMonth - ($offDay + $leave + $absent), 0)
                        : null,
                ];
            });
    }

    public function isMonthlyReport(): bool
    {
        return in_array(($this->data['report_type'] ?? 'monthly'), ['monthly', 'monthly_friday'], true);
    }

    public function getMonthlySummary(): array
    {
        $filters = $this->data ?? [];
        $selectedMonth = (int) ($filters['month'] ?? now()->format('n'));
        $selectedYear = (int) ($filters['year'] ?? now()->format('Y'));

        $summary = MonthlyReportSummary::query()
            ->where('year', $selectedYear)
            ->where('month', $selectedMonth)
            ->first();

        return [
            'room' => (int) ($summary?->room ?? 46),
            'consultant' => (int) ($summary?->consultant ?? 106),
            'occupied' => (int) ($summary?->occupied ?? 30),
        ];
    }

    public function isConsultantReport(): bool
    {
        return ($this->data['report_type'] ?? 'monthly') === 'consultant';
    }

    public function isPresenceDailyWeeklyReport(): bool
    {
        return ($this->data['report_type'] ?? 'monthly') === 'presence_daily_weekly';
    }

    public function isLateInEarlyOutReport(): bool
    {
        return ($this->data['report_type'] ?? 'monthly') === 'late_in_early_out';
    }

    public function isMonthlySummaryReport(): bool
    {
        return ($this->data['report_type'] ?? 'monthly') === 'monthly_summary';
    }

    public function getLateInRows(): Collection
    {
        return $this->buildLateInEarlyOutRows()['late_in'];
    }

    public function getEarlyOutRows(): Collection
    {
        return $this->buildLateInEarlyOutRows()['early_out'];
    }

    public function getPresenceDailyWeeklyRows(): Collection
    {
        $filters = $this->data ?? [];
        $selectedMonth = (int) ($filters['month'] ?? now()->format('n'));
        $selectedYear = (int) ($filters['year'] ?? now()->format('Y'));
        $timeFrame = $filters['time_frame'] ?? null;

        $query = InOut::query()
            ->selectRaw('in_outs.date, COUNT(DISTINCT in_outs.consultant_id) as total_total')
            ->whereYear('in_outs.date', $selectedYear)
            ->whereMonth('in_outs.date', $selectedMonth)
            ->whereNotNull('in_outs.in_time')
            ->groupBy('in_outs.date')
            ->orderBy('in_outs.date');

        $accessibleConsultantIds = array_map('intval', array_keys(ReportingResource::getAccessibleConsultantOptions()));
        if ($accessibleConsultantIds === []) {
            return collect();
        }

        $query->whereIn('in_outs.consultant_id', $accessibleConsultantIds);

        $this->applyInOutTimeFrameFilter($query, $timeFrame);

        $totalsByDate = $query
            ->get()
            ->mapWithKeys(fn ($row): array => [
                Carbon::parse($row->date)->toDateString() => (int) $row->total_total,
            ]);

        return $this->buildDailyWeeklyRows($selectedYear, $selectedMonth, $totalsByDate);
    }

    public function getPresenceDailyWeeklyTotals(): array
    {
        $rows = $this->getPresenceDailyWeeklyRows();

        return $this->sumDailyWeeklyRows($rows);
    }

    public function getPatientDailyWeeklyRows(): Collection
    {
        $filters = $this->data ?? [];
        $selectedMonth = (int) ($filters['month'] ?? now()->format('n'));
        $selectedYear = (int) ($filters['year'] ?? now()->format('Y'));
        $timeFrame = $filters['time_frame'] ?? null;

        $query = ReportingResource::getEloquentQuery()
            ->selectRaw('reportings.date, COALESCE(SUM(reportings.total), 0) as total_total')
            ->whereYear('reportings.date', $selectedYear)
            ->whereMonth('reportings.date', $selectedMonth)
            ->groupBy('reportings.date')
            ->orderBy('reportings.date');

        $this->applyTimeFrameFilter($query, $timeFrame);

        $totalsByDate = $query
            ->get()
            ->mapWithKeys(fn ($row): array => [
                Carbon::parse($row->date)->toDateString() => (int) $row->total_total,
            ]);

        return $this->buildDailyWeeklyRows($selectedYear, $selectedMonth, $totalsByDate);
    }

    public function getPatientDailyWeeklyTotals(): array
    {
        $rows = $this->getPatientDailyWeeklyRows();

        return $this->sumDailyWeeklyRows($rows);
    }

    public function getMonthlySummaryComparisonRows(): array
    {
        $filters = $this->data ?? [];
        $selectedMonth = (int) ($filters['month'] ?? now()->format('n'));
        $selectedYear = (int) ($filters['year'] ?? now()->format('Y'));
        $current = $this->buildMonthlySummaryMetrics($selectedYear, $selectedMonth);
        $previousDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->subMonth();
        $previous = $this->buildMonthlySummaryMetrics((int) $previousDate->format('Y'), (int) $previousDate->format('n'));

        return [
            'left' => [
                ['label' => 'Chamber (Room)', 'previous' => $previous['room'], 'current' => $current['room']],
                ['label' => 'Chamber Occupancy', 'previous' => $previous['occupied'], 'current' => $current['occupied']],
                ['label' => 'Total Consultant', 'previous' => $previous['consultant'], 'current' => $current['consultant']],
                ['label' => 'Total Chamber', 'previous' => $previous['total_chamber'], 'current' => $current['total_chamber']],
                ['label' => 'Consultants Leave', 'previous' => $previous['leave'], 'current' => $current['leave']],
                ['label' => 'Consultant Off day', 'previous' => $previous['off_day'], 'current' => $current['off_day']],
                ['label' => 'Consultant Absent', 'previous' => $previous['absent'], 'current' => $current['absent']],
                ['label' => 'Morning Consultant', 'previous' => $previous['morning_consultant'], 'current' => $current['morning_consultant']],
                ['label' => 'Evening Consultant', 'previous' => $previous['evening_consultant'], 'current' => $current['evening_consultant']],
                ['label' => 'Professor', 'previous' => $previous['professor'], 'current' => $current['professor']],
                ['label' => 'Associate Professor', 'previous' => $previous['associate_professor'], 'current' => $current['associate_professor']],
                ['label' => 'Assistant Professor', 'previous' => $previous['assistant_professor'], 'current' => $current['assistant_professor']],
                ['label' => 'Consultant', 'previous' => $previous['consultant_designation'], 'current' => $current['consultant_designation']],
            ],
            'right' => [
                ['label' => 'Total Patient', 'previous' => $previous['total_patient'], 'current' => $current['total_patient']],
                ['label' => 'Counseled', 'previous' => $previous['counseled'], 'current' => $current['counseled']],
                ['label' => 'Patient Left', 'previous' => $previous['patient_left'], 'current' => $current['patient_left']],
                ['label' => 'Con. without PT', 'previous' => $previous['consultant_without_pt'], 'current' => $current['consultant_without_pt']],
                ['label' => 'Total New Patient', 'previous' => $previous['total_new_patient'], 'current' => $current['total_new_patient']],
                ['label' => 'Report Checking PT', 'previous' => $previous['report_checking_pt'], 'current' => $current['report_checking_pt']],
                ['label' => 'Total Follow up PT', 'previous' => $previous['follow_up_pt'], 'current' => $current['follow_up_pt']],
                ['label' => 'Morning PT', 'previous' => $previous['morning_pt'], 'current' => $current['morning_pt']],
                ['label' => 'Evening PT', 'previous' => $previous['evening_pt'], 'current' => $current['evening_pt']],
                ['label' => 'Friday Consultant', 'previous' => $previous['friday_consultant'], 'current' => $current['friday_consultant']],
                ['label' => 'Friday PT', 'previous' => $previous['friday_pt'], 'current' => $current['friday_pt']],
                ['label' => 'Daily Ave. PT Turnover', 'previous' => $previous['daily_avg_pt_turnover'], 'current' => $current['daily_avg_pt_turnover']],
                ['label' => 'Daily Ave. PT per Doctor', 'previous' => $previous['daily_avg_pt_per_doctor'], 'current' => $current['daily_avg_pt_per_doctor']],
            ],
        ];
    }

    protected function sumDailyWeeklyRows(Collection $rows): array
    {
        $dayKeys = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];
        $totals = array_fill_keys($dayKeys, 0);
        $totals['total'] = 0;

        foreach ($rows as $row) {
            foreach ($dayKeys as $dayKey) {
                $totals[$dayKey] += (int) ($row[$dayKey] ?? 0);
            }

            $totals['total'] += (int) ($row['total'] ?? 0);
        }

        return $totals;
    }

    public function getReportPeriodLabel(): string
    {
        $filters = $this->data ?? [];
        $reportType = $filters['report_type'] ?? 'monthly';
        $timeFrameLabel = $this->getSelectedTimeFrameLabel();

        $label = match ($reportType) {
            'yearly' => 'Year: ' . ($filters['year'] ?? now()->format('Y')),
            'consultant' => 'Consultant: ' . $this->getConsultantLabel((string) ($filters['consultant_id'] ?? '')),
            'monthly_friday' => 'Fridays in ' . Carbon::createFromDate(
                (int) ($filters['year'] ?? now()->format('Y')),
                (int) ($filters['month'] ?? now()->format('n')),
                1,
            )->format('F Y'),
            'monthly_summary' => 'Monthly Report Summery: ' . Carbon::createFromDate(
                (int) ($filters['year'] ?? now()->format('Y')),
                (int) ($filters['month'] ?? now()->format('n')),
                1,
            )->format('F Y'),
            'presence_daily_weekly' => 'Month: ' . Carbon::createFromDate(
                (int) ($filters['year'] ?? now()->format('Y')),
                (int) ($filters['month'] ?? now()->format('n')),
                1,
            )->format('F-y'),
            'late_in_early_out' => 'Late In & Early Out: ' . Carbon::createFromDate(
                (int) ($filters['year'] ?? now()->format('Y')),
                (int) ($filters['month'] ?? now()->format('n')),
                1,
            )->format('F-y'),
            default => Carbon::createFromDate(
                (int) ($filters['year'] ?? now()->format('Y')),
                (int) ($filters['month'] ?? now()->format('n')),
                1,
            )->format('F Y'),
        };

        return $timeFrameLabel ? $label . ' | Time Frame: ' . $timeFrameLabel : $label;
    }

    /**
     * @return array<int, string>
     */
    protected function getMonthOptions(?int $year = null, ?string $reportType = null): array
    {
        $selectedYear = $year ?: (int) now()->format('Y');
        $query = in_array($reportType, ['presence_daily_weekly', 'late_in_early_out'], true)
            ? InOut::query()
            : ReportingResource::getEloquentQuery();

        if (in_array($reportType, ['presence_daily_weekly', 'late_in_early_out'], true)) {
            $accessibleConsultantIds = array_map('intval', array_keys(ReportingResource::getAccessibleConsultantOptions()));

            if ($accessibleConsultantIds === []) {
                return [];
            }

            $query
                ->where(function (Builder $query): void {
                    $query->whereNotNull('in_time')
                        ->orWhereNotNull('out_time');
                })
                ->whereIn('consultant_id', $accessibleConsultantIds);
        }

        return $query
            ->whereYear('date', $selectedYear)
            ->selectRaw('MONTH(date) as month_number')
            ->distinct()
            ->orderBy('month_number')
            ->pluck('month_number')
            ->mapWithKeys(fn ($month): array => [
                (int) $month => Carbon::createFromDate(2000, (int) $month, 1)->format('F'),
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function getYearOptions(?string $reportType = null): array
    {
        $query = in_array($reportType, ['presence_daily_weekly', 'late_in_early_out'], true)
            ? InOut::query()
            : ReportingResource::getEloquentQuery();

        if (in_array($reportType, ['presence_daily_weekly', 'late_in_early_out'], true)) {
            $accessibleConsultantIds = array_map('intval', array_keys(ReportingResource::getAccessibleConsultantOptions()));

            if ($accessibleConsultantIds === []) {
                return [];
            }

            $query
                ->where(function (Builder $query): void {
                    $query->whereNotNull('in_time')
                        ->orWhereNotNull('out_time');
                })
                ->whereIn('consultant_id', $accessibleConsultantIds);
        }

        return $query
            ->selectRaw('YEAR(date) as year_number')
            ->distinct()
            ->orderByDesc('year_number')
            ->pluck('year_number')
            ->mapWithKeys(fn ($year): array => [
                (int) $year => (int) $year,
            ])
            ->all();
    }

    protected function getConsultantLabel(string $consultantId): string
    {
        return ReportingResource::getAccessibleConsultantOptions()[$consultantId] ?? 'Select a consultant';
    }

    /**
     * @return array<string, string>
     */
    protected function getTimeFrameOptions(): array
    {
        return [
            '07:00:00-14:00:00' => '7AM-2PM',
            '14:00:00-17:00:00' => '2PM-5PM',
            '17:00:00-19:00:00' => '5PM-7PM',
            '19:00:00-23:00:00' => '7PM-11PM',
        ];
    }

    protected function getSelectedTimeFrameLabel(): ?string
    {
        $timeFrame = $this->data['time_frame'] ?? null;

        if (blank($timeFrame)) {
            return null;
        }

        return $this->getTimeFrameOptions()[$timeFrame] ?? null;
    }

    protected function countFridaysInMonth(int $year, int $month): int
    {
        $date = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $count = 0;

        while ($date->lte($endOfMonth)) {
            if ($date->dayOfWeek === Carbon::FRIDAY) {
                $count++;
            }

            $date->addDay();
        }

        return $count;
    }

    public function resolveFormData(Request $request): array
    {
        $defaults = $this->getDefaultFormData();
        $requested = array_filter(
            $request->only(['report_type', 'month', 'year', 'consultant_id', 'time_frame']),
            fn ($value): bool => $value !== null && $value !== ''
        );

        $data = array_merge($defaults, $requested);
        $yearOptions = $this->getYearOptions($data['report_type'] ?? 'monthly');

        if (! array_key_exists((int) $data['year'], $yearOptions) && $yearOptions !== []) {
            $data['year'] = array_key_first($yearOptions);
        }

        $data['year'] = (int) $data['year'];

        $monthOptions = $this->getMonthOptions($data['year'], $data['report_type'] ?? 'monthly');
        if (in_array($data['report_type'], ['monthly', 'monthly_friday', 'monthly_summary', 'presence_daily_weekly', 'late_in_early_out'], true) && $monthOptions !== []) {
            $month = (int) ($data['month'] ?? 0);
            $data['month'] = array_key_exists($month, $monthOptions)
                ? $month
                : array_key_first($monthOptions);
        }

        $consultantOptions = ReportingResource::getAccessibleConsultantOptions();
        if ($consultantOptions !== []) {
            $consultantId = (string) ($data['consultant_id'] ?? '');
            if (! array_key_exists($consultantId, $consultantOptions) && ! array_key_exists((int) $consultantId, $consultantOptions)) {
                $data['consultant_id'] = array_key_first($consultantOptions);
            }
        }

        $timeFrameOptions = $this->getTimeFrameOptions();
        if (! blank($data['time_frame'] ?? null) && ! array_key_exists($data['time_frame'], $timeFrameOptions)) {
            $data['time_frame'] = null;
        }

        return $data;
    }

    protected function getDefaultFormData(): array
    {
        $consultantOptions = ReportingResource::getAccessibleConsultantOptions();
        $latestReportingDate = ReportingResource::getEloquentQuery()
            ->latest('date')
            ->value('date');
        $defaultDate = $latestReportingDate
            ? Carbon::parse($latestReportingDate)
            : now();

        return [
            'report_type' => 'monthly',
            'month' => (int) $defaultDate->format('n'),
            'year' => (int) $defaultDate->format('Y'),
            'consultant_id' => array_key_first($consultantOptions),
            'time_frame' => null,
        ];
    }

    protected function getPrintQueryParameters(): array
    {
        $filters = $this->data ?? [];
        $parameters = [
            'report_type' => $filters['report_type'] ?? 'monthly',
        ];

        if (in_array($parameters['report_type'], ['monthly', 'monthly_friday', 'monthly_summary', 'presence_daily_weekly', 'late_in_early_out', 'yearly'], true)) {
            $parameters['year'] = (int) ($filters['year'] ?? now()->format('Y'));
        }

        if (in_array($parameters['report_type'], ['monthly', 'monthly_friday', 'monthly_summary', 'presence_daily_weekly', 'late_in_early_out'], true)) {
            $parameters['month'] = (int) ($filters['month'] ?? now()->format('n'));
        }

        if ($parameters['report_type'] === 'consultant') {
            $parameters['consultant_id'] = $filters['consultant_id'] ?? null;
        }

        if (filled($filters['time_frame'] ?? null)) {
            $parameters['time_frame'] = $filters['time_frame'];
        }

        return array_filter($parameters, fn ($value): bool => $value !== null && $value !== '');
    }

    protected function applyTimeFrameFilter($query, ?string $timeFrame): void
    {
        if (blank($timeFrame)) {
            return;
        }

        [$startTime, $endTime] = explode('-', $timeFrame);
        $isLastTimeFrame = $timeFrame === '19:00:00-23:00:00';

        $query
            ->join('in_outs', function ($join): void {
                $join->on('in_outs.consultant_id', '=', 'reportings.consultant_id')
                    ->on('in_outs.date', '=', 'reportings.date');
            })
            ->where('in_outs.in_time', '>=', $startTime)
            ->where('in_outs.in_time', $isLastTimeFrame ? '<=' : '<', $endTime);
    }

    protected function applyInOutTimeFrameFilter($query, ?string $timeFrame): void
    {
        if (blank($timeFrame)) {
            return;
        }

        [$startTime, $endTime] = explode('-', $timeFrame);
        $isLastTimeFrame = $timeFrame === '19:00:00-23:00:00';

        $query
            ->where('in_outs.in_time', '>=', $startTime)
            ->where('in_outs.in_time', $isLastTimeFrame ? '<=' : '<', $endTime);
    }

    protected function buildLateInEarlyOutRows(): array
    {
        $filters = $this->data ?? [];
        $selectedMonth = (int) ($filters['month'] ?? now()->format('n'));
        $selectedYear = (int) ($filters['year'] ?? now()->format('Y'));
        $timeFrame = $filters['time_frame'] ?? null;
        $accessibleConsultantIds = array_map('intval', array_keys(ReportingResource::getAccessibleConsultantOptions()));

        if ($accessibleConsultantIds === []) {
            return [
                'late_in' => collect(),
                'early_out' => collect(),
            ];
        }

        $query = InOut::query()
            ->join('consultants', 'in_outs.consultant_id', '=', 'consultants.id')
            ->join('departments', 'in_outs.department_id', '=', 'departments.id')
            ->whereYear('in_outs.date', $selectedYear)
            ->whereMonth('in_outs.date', $selectedMonth)
            ->whereIn('in_outs.consultant_id', $accessibleConsultantIds)
            ->orderBy('consultants.name')
            ->orderBy('in_outs.date')
            ->select([
                'in_outs.date',
                'in_outs.in_time',
                'in_outs.out_time',
                'consultants.name as consultant_name',
                'consultants.chamber_time',
                'departments.name as department_name',
            ]);

        $this->applyInOutTimeFrameFilter($query, $timeFrame);

        $lateInRows = collect();
        $earlyOutRows = collect();

        foreach ($query->get() as $row) {
            $chamberRange = $this->parseChamberTimeRange($row->chamber_time);

            if ($chamberRange['start'] && filled($row->in_time)) {
                $lateInMinutes = $this->calculatePositiveMinutesDifference($chamberRange['start'], (string) $row->in_time);

                if ($lateInMinutes > 0) {
                    $lateInRows->push([
                        'consultant_name' => $row->consultant_name ?? '-',
                        'department_name' => $row->department_name ?? '-',
                        'chamber_time' => $chamberRange['label'],
                        'time' => $this->formatDisplayTime((string) $row->in_time),
                        'difference' => $this->formatMinutesAsDuration($lateInMinutes),
                    ]);
                }
            }

            if ($chamberRange['end'] && filled($row->out_time)) {
                $earlyOutMinutes = $this->calculatePositiveMinutesDifference((string) $row->out_time, $chamberRange['end']);

                if ($earlyOutMinutes > 0) {
                    $earlyOutRows->push([
                        'consultant_name' => $row->consultant_name ?? '-',
                        'department_name' => $row->department_name ?? '-',
                        'chamber_time' => $chamberRange['label'],
                        'time' => $this->formatDisplayTime((string) $row->out_time),
                        'difference' => $this->formatMinutesAsDuration($earlyOutMinutes),
                    ]);
                }
            }
        }

        return [
            'late_in' => $lateInRows,
            'early_out' => $earlyOutRows,
        ];
    }

    protected function parseChamberTimeRange(?string $chamberTime): array
    {
        $label = filled($chamberTime) ? trim((string) $chamberTime) : '-';

        if (blank($chamberTime)) {
            return ['label' => '-', 'start' => null, 'end' => null];
        }

        $parts = preg_split('/\s*[-–]\s*/', trim((string) $chamberTime)) ?: [];
        $startRaw = $parts[0] ?? null;
        $endRaw = $parts[1] ?? $startRaw;

        $start = $this->normalizeLooseTimeValue($startRaw, $endRaw);
        $end = $this->normalizeLooseTimeValue($endRaw, $startRaw);

        return [
            'label' => $label,
            'start' => $start,
            'end' => $end,
        ];
    }

    protected function normalizeLooseTimeValue(?string $value, ?string $fallbackValue = null): ?string
    {
        if (blank($value)) {
            return null;
        }

        $candidate = strtoupper(trim((string) $value));
        $fallback = strtoupper(trim((string) ($fallbackValue ?? '')));

        if (! str_contains($candidate, 'AM') && ! str_contains($candidate, 'PM')) {
            if (str_contains($fallback, 'AM')) {
                $candidate .= ' AM';
            } elseif (str_contains($fallback, 'PM')) {
                $candidate .= ' PM';
            }
        }

        $candidate = preg_replace('/\s+/', ' ', $candidate) ?? $candidate;

        foreach (['g:i A', 'g A', 'g:iA', 'gA', 'H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $candidate)->format('H:i:s');
            } catch (\Throwable $exception) {
                continue;
            }
        }

        try {
            return Carbon::parse($candidate)->format('H:i:s');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function calculatePositiveMinutesDifference(string $fromTime, string $toTime): int
    {
        try {
            $from = Carbon::createFromFormat('H:i:s', $fromTime);
            $to = Carbon::createFromFormat('H:i:s', $toTime);
        } catch (\Throwable $exception) {
            return 0;
        }

        if ($to->lessThanOrEqualTo($from)) {
            return 0;
        }

        return $from->diffInMinutes($to);
    }

    protected function formatDisplayTime(?string $time): string
    {
        if (blank($time)) {
            return '-';
        }

        try {
            return Carbon::createFromFormat('H:i:s', (string) $time)->format('h:i A');
        } catch (\Throwable $exception) {
            return (string) $time;
        }
    }

    protected function formatMinutesAsDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return sprintf('%dh %dm', $hours, $remainingMinutes);
        }

        if ($hours > 0) {
            return sprintf('%dh', $hours);
        }

        return sprintf('%dm', $remainingMinutes);
    }

    protected function formatWeekOrdinal(int $weekNumber): string
    {
        return match ($weekNumber) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => $weekNumber . 'th',
        };
    }

    protected function buildDailyWeeklyRows(int $year, int $month, Collection $totalsByDate): Collection
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $dayOrder = [6, 0, 1, 2, 3, 4, 5];
        $dayKeys = [
            6 => 'sat',
            0 => 'sun',
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
        ];
        $dayLabels = [
            'sat' => 'Sat',
            'sun' => 'Sun',
            'mon' => 'Mon',
            'tue' => 'Tue',
            'wed' => 'Wed',
            'thu' => 'Thu',
            'fri' => 'Fri',
        ];

        $weeks = collect();
        $date = $startOfMonth->copy();

        while ($date->lte($endOfMonth)) {
            $weekNumber = (int) ceil($date->day / 7);
            $weekKey = 'week_' . $weekNumber;
            $dayKey = $dayKeys[$date->dayOfWeek];

            if (! $weeks->has($weekKey)) {
                $weeks->put($weekKey, [
                    'week_label' => $this->formatWeekOrdinal($weekNumber),
                    'sat' => null,
                    'sun' => null,
                    'mon' => null,
                    'tue' => null,
                    'wed' => null,
                    'thu' => null,
                    'fri' => null,
                    'total' => 0,
                ]);
            }

            $week = $weeks->get($weekKey);
            $value = (int) ($totalsByDate->get($date->toDateString()) ?? 0);
            $week[$dayKey] = $value > 0 ? $value : null;
            $week['total'] += $value;
            $weeks->put($weekKey, $week);

            $date->addDay();
        }

        return $weeks
            ->values()
            ->map(function (array $week) use ($dayOrder, $dayKeys, $dayLabels): array {
                foreach ($dayOrder as $dayOfWeek) {
                    $key = $dayKeys[$dayOfWeek];
                    $week['days'][$key] = [
                        'label' => $dayLabels[$key],
                        'value' => $week[$key],
                    ];
                }

                return $week;
            });
    }

    protected function buildMonthlySummaryMetrics(int $year, int $month): array
    {
        $monthlyRows = $this->buildMonthlyAggregatedRows($year, $month);
        $monthlySummary = MonthlyReportSummary::query()
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $consultants = Consultant::query()
            ->when(method_exists(ReportingResource::class, 'getAccessibleConsultantsQuery'), function ($query) {
                $accessibleIds = array_map('intval', array_keys(ReportingResource::getAccessibleConsultantOptions()));
                if ($accessibleIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $accessibleIds);
                }
            })
            ->get();

        $reportingTotals = ReportingResource::getEloquentQuery()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('
                COALESCE(SUM(total), 0) as total_patient,
                COALESCE(SUM(report + follow_up + back), 0) as counseled,
                COALESCE(SUM(back), 0) as patient_left,
                COALESCE(SUM(new), 0) as total_new_patient,
                COALESCE(SUM(report), 0) as report_checking_pt,
                COALESCE(SUM(follow_up), 0) as follow_up_pt
            ')
            ->first();

        $consultantWithoutPt = ReportingResource::getEloquentQuery()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('consultant_id, COALESCE(SUM(total), 0) as total_total')
            ->groupBy('consultant_id')
            ->get()
            ->filter(fn ($row): bool => (int) $row->total_total === 0)
            ->count();

        $morningPt = ReportingResource::getEloquentQuery()
            ->join('in_outs', function ($join): void {
                $join->on('in_outs.consultant_id', '=', 'reportings.consultant_id')
                    ->on('in_outs.date', '=', 'reportings.date');
            })
            ->whereYear('reportings.date', $year)
            ->whereMonth('reportings.date', $month)
            ->where('in_outs.in_time', '>=', '07:00:00')
            ->where('in_outs.in_time', '<', '14:00:00')
            ->sum('reportings.total');

        $eveningPt = ReportingResource::getEloquentQuery()
            ->join('in_outs', function ($join): void {
                $join->on('in_outs.consultant_id', '=', 'reportings.consultant_id')
                    ->on('in_outs.date', '=', 'reportings.date');
            })
            ->whereYear('reportings.date', $year)
            ->whereMonth('reportings.date', $month)
            ->where('in_outs.in_time', '>=', '14:00:00')
            ->where('in_outs.in_time', '<=', '23:00:00')
            ->sum('reportings.total');

        $fridayConsultant = ReportingResource::getEloquentQuery()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereRaw('DAYOFWEEK(date) = 6')
            ->distinct('consultant_id')
            ->count('consultant_id');

        $fridayPt = ReportingResource::getEloquentQuery()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereRaw('DAYOFWEEK(date) = 6')
            ->sum('total');

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $totalPatient = (int) ($reportingTotals?->total_patient ?? 0);
        $doctorCount = max((int) ($monthlySummary?->consultant ?? 106), 1);

        return [
            'room' => (int) ($monthlySummary?->room ?? 46),
            'occupied' => (int) ($monthlySummary?->occupied ?? 30),
            'consultant' => (int) ($monthlySummary?->consultant ?? 106),
            'total_chamber' => (int) $monthlyRows->sum('chamber'),
            'leave' => (int) $monthlyRows->sum('leave'),
            'off_day' => (int) $monthlyRows->sum('off_day'),
            'absent' => (int) $monthlyRows->sum('absent'),
            'morning_consultant' => $consultants->filter(fn ($consultant): bool => str_contains(strtoupper((string) $consultant->chamber_time), 'AM'))->count(),
            'evening_consultant' => $consultants->filter(fn ($consultant): bool => str_contains(strtoupper((string) $consultant->chamber_time), 'PM'))->count(),
            'professor' => $consultants->filter(fn ($consultant): bool => str_contains(strtolower((string) $consultant->designation), 'professor') && ! str_contains(strtolower((string) $consultant->designation), 'associate') && ! str_contains(strtolower((string) $consultant->designation), 'assistant'))->count(),
            'associate_professor' => $consultants->filter(fn ($consultant): bool => str_contains(strtolower((string) $consultant->designation), 'associate professor'))->count(),
            'assistant_professor' => $consultants->filter(fn ($consultant): bool => str_contains(strtolower((string) $consultant->designation), 'assistant professor'))->count(),
            'consultant_designation' => $consultants->filter(fn ($consultant): bool => str_contains(strtolower((string) $consultant->designation), 'consultant'))->count(),
            'total_patient' => $totalPatient,
            'counseled' => (int) ($reportingTotals?->counseled ?? 0),
            'patient_left' => (int) ($reportingTotals?->patient_left ?? 0),
            'consultant_without_pt' => $consultantWithoutPt,
            'total_new_patient' => (int) ($reportingTotals?->total_new_patient ?? 0),
            'report_checking_pt' => (int) ($reportingTotals?->report_checking_pt ?? 0),
            'follow_up_pt' => (int) ($reportingTotals?->follow_up_pt ?? 0),
            'morning_pt' => (int) $morningPt,
            'evening_pt' => (int) $eveningPt,
            'friday_consultant' => (int) $fridayConsultant,
            'friday_pt' => (int) $fridayPt,
            'daily_avg_pt_turnover' => $daysInMonth > 0 ? round($totalPatient / $daysInMonth, 2) : 0,
            'daily_avg_pt_per_doctor' => $doctorCount > 0 ? round($totalPatient / $doctorCount, 2) : 0,
        ];
    }

    protected function buildMonthlyAggregatedRows(int $year, int $month): Collection
    {
        $query = ReportingResource::getEloquentQuery()
            ->leftJoin('consultants', 'reportings.consultant_id', '=', 'consultants.id')
            ->leftJoin('departments', 'reportings.department_id', '=', 'departments.id')
            ->leftJoin('remarks', 'reportings.remark_id', '=', 'remarks.id')
            ->selectRaw('
                reportings.consultant_id,
                consultants.name as consultant_name,
                departments.name as department_name,
                COALESCE(SUM(reportings.new), 0) as new_total,
                COALESCE(SUM(reportings.report), 0) as report_total,
                COALESCE(SUM(reportings.follow_up), 0) as follow_up_total,
                COALESCE(SUM(reportings.back), 0) as back_total,
                COALESCE(SUM(reportings.total), 0) as total_total,
                COUNT(DISTINCT CASE
                    WHEN LOWER(REPLACE(remarks.name, " ", "")) IN ("offday", "off-day")
                    THEN reportings.date
                END) as off_day_total,
                COUNT(DISTINCT CASE
                    WHEN LOWER(remarks.name) = "leave"
                    THEN reportings.date
                END) as leave_total,
                COUNT(DISTINCT CASE
                    WHEN LOWER(remarks.name) = "absent"
                    THEN reportings.date
                END) as absent_total
            ')
            ->whereYear('reportings.date', $year)
            ->whereMonth('reportings.date', $month)
            ->groupBy('reportings.consultant_id', 'consultants.name', 'departments.name')
            ->orderBy('consultants.name');

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        return $query
            ->get()
            ->map(function ($row) use ($daysInMonth): array {
                $offDay = (int) $row->off_day_total;
                $leave = (int) $row->leave_total;
                $absent = (int) $row->absent_total;

                return [
                    'consultant_name' => $row->consultant_name ?? '-',
                    'department_name' => $row->department_name ?? '-',
                    'new' => (int) $row->new_total,
                    'report' => (int) $row->report_total,
                    'follow_up' => (int) $row->follow_up_total,
                    'back' => (int) $row->back_total,
                    'total' => (int) $row->total_total,
                    'off_day' => $offDay,
                    'leave' => $leave,
                    'absent' => $absent,
                    'chamber' => max($daysInMonth - ($offDay + $leave + $absent), 0),
                ];
            });
    }
}
