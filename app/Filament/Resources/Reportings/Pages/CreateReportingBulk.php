<?php

namespace App\Filament\Resources\Reportings\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Resources\Reportings\ReportingResource;
use App\Models\Consultant;
use App\Models\Remark;
use App\Models\Reporting;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReportingBulk extends CreateRecord
{
    use InteractsWithForms;
    protected static string $resource = ReportingResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $heading = 'Bulk Create Reporting';

    protected ?string $subheading = 'Choose a date, enter values for the consultants you need, and save them together.';

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill([
            'date' => Carbon::now()->toDateString(),
            'rows' => $this->getBulkRows(),
        ]);

        $this->callHook('afterFill');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Bulk Reporting')
                    ->heading(auth()->user()?->isSuperAdmin() ? 'Bulk Reporting' : 'Reporting')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('date')
                            ->required()
                            ->default(Carbon::now()->toDateString())
                            ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                        Repeater::make('rows')
                            ->label('Consultants')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsible(false)
                            ->columnSpanFull()
                            ->schema([
                                Hidden::make('consultant_id'),
                                Hidden::make('department_id'),
                                TextInput::make('consultant_name')
                                    ->label('Consultant')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('department_name')
                                    ->label('Department')
                                    ->disabled()
                                    ->dehydrated(false),
                                $this->makeBulkNumberInput('new'),
                                $this->makeBulkNumberInput('report'),
                                $this->makeBulkNumberInput('follow_up')
                                    ->label('Follow-up'),
                                $this->makeBulkNumberInput('back'),
                                TextInput::make('total')
                                    ->integer()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false),
                                Select::make('remark_id')
                                    ->label('Remark')
                                    ->placeholder('Remark')
                                    ->options(fn (): array => Remark::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable(),
                            ])
                            ->columns(8),
                    ]),
            ]);
    }

    protected function makeBulkNumberInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->inputMode('decimal')
            ->autocomplete(false)
            ->minValue(0)
            ->default(0)
            ->extraInputAttributes([
                'x-on:focus' => '$el.select()',
            ])
            ->afterStateUpdatedJs($this->getRowTotalUpdateJs());
    }

    public function create(bool $another = false): void
    {
        if ($this->isCreating) {
            return;
        }

        $this->isCreating = true;

        $this->authorizeAccess();

        try {
            $data = $this->form->getState();
            $date = $this->resolveBulkDate($data);
            $rows = collect($data['rows'] ?? [])
                ->filter(fn (array $row): bool => $this->rowHasInput($row));

            if (blank($date)) {
                throw ValidationException::withMessages([
                    'data.date' => 'The date field is required.',
                ]);
            }

            if ($rows->isEmpty()) {
                Notification::make()
                    ->title('No reporting rows to save')
                    ->body('Enter values or choose a remark for at least one consultant.')
                    ->warning()
                    ->send();

                $this->isCreating = false;

                return;
            }

            $duplicateConsultantIds = Reporting::query()
                ->whereDate('date', $date)
                ->whereIn('consultant_id', $rows->pluck('consultant_id'))
                ->pluck('consultant_id')
                ->all();

            $rowsToInsert = $rows
                ->reject(fn (array $row): bool => in_array($row['consultant_id'], $duplicateConsultantIds, true))
                ->values();

            $skippedConsultantNames = $rows
                ->filter(fn (array $row): bool => in_array($row['consultant_id'], $duplicateConsultantIds, true))
                ->map(fn (array $row): string => $this->resolveConsultantName($row))
                ->values();

            if ($rowsToInsert->isEmpty()) {
                Notification::make()
                    ->title('No new reporting entries were saved')
                    ->body($this->buildSkippedMessage($skippedConsultantNames->all(), $date))
                    ->warning()
                    ->send();

                return;
            }

            DB::transaction(function () use ($rowsToInsert, $date): void {
                foreach ($rowsToInsert as $row) {
                    Reporting::create([
                        'date' => $date,
                        'consultant_id' => $row['consultant_id'],
                        'department_id' => $row['department_id'],
                        'new' => (int) ($row['new'] ?? 0),
                        'report' => (int) ($row['report'] ?? 0),
                        'follow_up' => (int) ($row['follow_up'] ?? 0),
                        'back' => (int) ($row['back'] ?? 0),
                        'total' => $this->calculateTotal($row),
                        'remark_id' => $row['remark_id'] ?? null,
                    ]);
                }
            });
        } finally {
            $this->isCreating = false;
        }

        $notification = Notification::make()
            ->title($skippedConsultantNames->isNotEmpty() ? 'Bulk reporting partially created' : 'Bulk reporting created')
            ->body($this->buildSaveMessage($rowsToInsert->count(), $skippedConsultantNames->all(), $date));

        if ($skippedConsultantNames->isNotEmpty()) {
            $notification->warning();
        } else {
            $notification->success();
        }

        $notification->send();

        $this->redirect($this->getRedirectUrl());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label(auth()->user()?->isSuperAdmin() ? 'Save Bulk Reporting' : 'Submit Now')
                ->submit('create')
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction(),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return auth()->user()?->isSuperAdmin()
            ? 'Bulk Create Reporting'
            : "Today's Reporting";
    }

    public function getHeading(): string | Htmlable
    {
        return auth()->user()?->isSuperAdmin()
            ? 'Bulk Create Reporting'
            : "Today's Reporting";
    }

    public function getSubheading(): string | Htmlable | null
    {
        return auth()->user()?->isSuperAdmin()
            ? 'Choose a date, enter values for the consultants you need, and save them together.'
            : 'Enter today\'s reporting values for your assigned consultants.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveBulkDate(array $data): ?string
    {
        if (! (auth()->user()?->isSuperAdmin() ?? false)) {
            return Carbon::now()->toDateString();
        }

        return $data['date'] ?? null;
    }

    /**
     * @return array<int, array<string, int|string|null>>
     */
    protected function getBulkRows(): array
    {
        return ReportingResource::getAccessibleConsultantsQuery()
            ->with('department')
            ->get()
            ->map(fn (Consultant $consultant): array => [
                'consultant_id' => $consultant->id,
                'consultant_name' => $consultant->name,
                'department_id' => $consultant->department_id,
                'department_name' => $consultant->department?->name,
                'new' => 0,
                'report' => 0,
                'follow_up' => 0,
                'back' => 0,
                'total' => 0,
                'remark_id' => null,
            ])
            ->all();
    }

    protected function getRowTotalUpdateJs(): string
    {
        return <<<'JS'
            $set(
                'total',
                Number($get('new') || 0)
                    + Number($get('report') || 0)
                    + Number($get('follow_up') || 0)
                    + Number($get('back') || 0)
            )
        JS;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function resolveConsultantName(array $row): string
    {
        if (filled($row['consultant_name'] ?? null)) {
            return (string) $row['consultant_name'];
        }

        $consultantId = $row['consultant_id'] ?? null;

        if (blank($consultantId)) {
            return 'This consultant';
        }

        return (string) (Consultant::query()->find($consultantId)?->name ?? 'This consultant');
    }

    /**
     * @param  array<int, string>  $skippedConsultantNames
     */
    protected function buildSaveMessage(int $savedCount, array $skippedConsultantNames, string $date): string
    {
        $savedMessage = "{$savedCount} reporting entr" . ($savedCount === 1 ? 'y was' : 'ies were') . ' saved successfully.';

        if ($skippedConsultantNames === []) {
            return $savedMessage;
        }

        return $savedMessage . ' ' . $this->buildSkippedMessage($skippedConsultantNames, $date);
    }

    /**
     * @param  array<int, string>  $skippedConsultantNames
     */
    protected function buildSkippedMessage(array $skippedConsultantNames, string $date): string
    {
        $names = implode(', ', array_unique($skippedConsultantNames));

        return "Skipped existing consultants for {$date}: {$names}.";
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function rowHasInput(array $row): bool
    {
        return $this->calculateTotal($row) > 0 || filled($row['remark_id'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function calculateTotal(array $row): int
    {
        return (int) ($row['new'] ?? 0)
            + (int) ($row['report'] ?? 0)
            + (int) ($row['follow_up'] ?? 0)
            + (int) ($row['back'] ?? 0);
    }

    protected function getRedirectUrl(): string
    {
        if (! (auth()->user()?->isSuperAdmin() ?? false)) {
            return Dashboard::getUrl(panel: 'sysadmin');
        }

        return static::getResource()::getUrl();
    }
}
