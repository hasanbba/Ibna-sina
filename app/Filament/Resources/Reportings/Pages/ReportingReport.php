<?php

namespace App\Filament\Resources\Reportings\Pages;

use App\Filament\Resources\Reportings\ReportingResource;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
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

        $this->form->fill([
            'report_type' => 'monthly',
            'month' => (int) now()->format('n'),
            'year' => (int) now()->format('Y'),
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
                                'yearly' => 'Yearly Report',
                                'consultant' => 'Consultant Wise Report',
                            ])
                            ->default('monthly')
                            ->live()
                            ->required(),
                        Select::make('month')
                            ->options($this->getMonthOptions())
                            ->default((int) now()->format('n'))
                            ->visible(fn (Get $get): bool => $get('report_type') === 'monthly')
                            ->required(fn (Get $get): bool => $get('report_type') === 'monthly'),
                        TextInput::make('year')
                            ->numeric()
                            ->default((int) now()->format('Y'))
                            ->minValue(2000)
                            ->maxValue((int) now()->addYear()->format('Y'))
                            ->required(fn (Get $get): bool => in_array($get('report_type'), ['monthly', 'yearly'], true)),
                        Select::make('consultant_id')
                            ->label('Consultant')
                            ->options(fn (): array => ReportingResource::getAccessibleConsultantOptions())
                            ->searchable()
                            ->preload()
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

        $query = ReportingResource::getEloquentQuery()
            ->leftJoin('consultants', 'reportings.consultant_id', '=', 'consultants.id')
            ->leftJoin('departments', 'reportings.department_id', '=', 'departments.id')
            ->selectRaw('
                reportings.consultant_id,
                consultants.name as consultant_name,
                departments.name as department_name,
                COALESCE(SUM(reportings.new), 0) as new_total,
                COALESCE(SUM(reportings.report), 0) as report_total,
                COALESCE(SUM(reportings.follow_up), 0) as follow_up_total,
                COALESCE(SUM(reportings.back), 0) as back_total,
                COALESCE(SUM(reportings.total), 0) as total_total
            ')
            ->groupBy('reportings.consultant_id', 'consultants.name', 'departments.name')
            ->orderBy('consultants.name');

        if ($reportType === 'monthly') {
            $year = (int) ($filters['year'] ?? now()->format('Y'));
            $month = (int) ($filters['month'] ?? now()->format('n'));

            $query
                ->whereYear('reportings.date', $year)
                ->whereMonth('reportings.date', $month);
        }

        if ($reportType === 'yearly') {
            $year = (int) ($filters['year'] ?? now()->format('Y'));

            $query->whereYear('reportings.date', $year);
        }

        if ($reportType === 'consultant') {
            $consultantId = $filters['consultant_id'] ?? null;

            if (blank($consultantId)) {
                return collect();
            }

            $query->where('reportings.consultant_id', $consultantId);
        }

        return $query
            ->get()
            ->map(fn ($row): array => [
                'consultant_name' => $row->consultant_name ?? '-',
                'department_name' => $row->department_name ?? '-',
                'new' => (int) $row->new_total,
                'report' => (int) $row->report_total,
                'follow_up' => (int) $row->follow_up_total,
                'back' => (int) $row->back_total,
                'total' => (int) $row->total_total,
            ]);
    }

    public function getReportPeriodLabel(): string
    {
        $filters = $this->data ?? [];
        $reportType = $filters['report_type'] ?? 'monthly';

        return match ($reportType) {
            'yearly' => 'Year: ' . ($filters['year'] ?? now()->format('Y')),
            'consultant' => 'Consultant: ' . $this->getConsultantLabel((string) ($filters['consultant_id'] ?? '')),
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
    protected function getMonthOptions(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [$month => Carbon::createFromDate(2000, $month, 1)->format('F')])
            ->all();
    }

    protected function getConsultantLabel(string $consultantId): string
    {
        return ReportingResource::getAccessibleConsultantOptions()[$consultantId] ?? 'Select a consultant';
    }
}
