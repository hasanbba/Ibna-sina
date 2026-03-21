<?php

namespace App\Filament\Resources\Reportings\Pages;

use App\Models\Investigation;
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
use Illuminate\Support\Collection;

class ReportingReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ReportingResource::class;

    protected string $view = 'filament.resources.reportings.pages.reporting-report';

    public ?array $data = [];

    public function mount(): void
    {
        $consultantOptions = ReportingResource::getAccessibleConsultantOptions();
        $latestReportingDate = ReportingResource::getEloquentQuery()
            ->latest('date')
            ->value('date');
        $defaultDate = $latestReportingDate
            ? Carbon::parse($latestReportingDate)
            : now();

        $this->form->fill([
            'report_type' => 'monthly',
            'month' => (int) $defaultDate->format('n'),
            'year' => (int) $defaultDate->format('Y'),
            'consultant_id' => array_key_first($consultantOptions),
        ]);
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
                                'yearly' => 'Yearly Report',
                                'consultant' => 'Consultant Wise Report',
                            ])
                            ->default('monthly')
                            ->live()
                            ->required(),
                        Select::make('month')
                            ->options(fn (Get $get): array => $this->getMonthOptions((int) ($get('year') ?? now()->format('Y'))))
                            ->placeholder('Select available month')
                            ->visible(fn (Get $get): bool => in_array($get('report_type'), ['monthly', 'monthly_friday'], true))
                            ->required(fn (Get $get): bool => in_array($get('report_type'), ['monthly', 'monthly_friday'], true)),
                        Select::make('year')
                            ->options($this->getYearOptions())
                            ->placeholder('Select available year')
                            ->default(fn (): ?int => isset($this->data['year']) ? (int) $this->data['year'] : null)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                $monthOptions = $this->getMonthOptions((int) $state);
                                $selectedMonth = (int) ($get('month') ?? 0);

                                if ($monthOptions === []) {
                                    $set('month', null);

                                    return;
                                }

                                if (! array_key_exists($selectedMonth, $monthOptions)) {
                                    $set('month', array_key_first($monthOptions));
                                }
                            })
                            ->required(fn (Get $get): bool => in_array($get('report_type'), ['monthly', 'monthly_friday', 'yearly'], true)),
                        Select::make('consultant_id')
                            ->label('Consultant')
                            ->options(fn (): array => ReportingResource::getAccessibleConsultantOptions())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get): bool => $get('report_type') === 'consultant')
                            ->required(fn (Get $get): bool => $get('report_type') === 'consultant'),
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

    public function getReportRows(): Collection
    {
        $filters = $this->data ?? [];
        $reportType = $filters['report_type'] ?? 'monthly';
        $selectedMonth = (int) ($filters['month'] ?? now()->format('n'));
        $selectedYear = (int) ($filters['year'] ?? now()->format('Y'));
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

            return ReportingResource::getEloquentQuery()
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
                ])
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

    public function isConsultantReport(): bool
    {
        return ($this->data['report_type'] ?? 'monthly') === 'consultant';
    }

    public function getReportPeriodLabel(): string
    {
        $filters = $this->data ?? [];
        $reportType = $filters['report_type'] ?? 'monthly';

        return match ($reportType) {
            'yearly' => 'Year: ' . ($filters['year'] ?? now()->format('Y')),
            'consultant' => 'Consultant: ' . $this->getConsultantLabel((string) ($filters['consultant_id'] ?? '')),
            'monthly_friday' => 'Fridays in ' . Carbon::createFromDate(
                (int) ($filters['year'] ?? now()->format('Y')),
                (int) ($filters['month'] ?? now()->format('n')),
                1,
            )->format('F Y'),
            default => Carbon::createFromDate(
                (int) ($filters['year'] ?? now()->format('Y')),
                (int) ($filters['month'] ?? now()->format('n')),
                1,
            )->format('F Y'),
        };
    }

    /**
     * @return array<int, string>
     */
    protected function getMonthOptions(?int $year = null): array
    {
        $selectedYear = $year ?: (int) now()->format('Y');

        return ReportingResource::getEloquentQuery()
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
    protected function getYearOptions(): array
    {
        return ReportingResource::getEloquentQuery()
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
}
