<?php

namespace App\Filament\Resources\InOuts\Pages;

use App\Filament\Resources\InOuts\InOutResource;
use App\Models\Consultant;
use App\Models\InOut;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateInOutBulk extends CreateRecord
{
    use InteractsWithForms;

    protected static string $resource = InOutResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $subheading = 'Enter in and out time for consultants.';

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
                Section::make('Bulk In & Out')
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
                                ...$this->getInOutFields(),
                            ])
                            ->columns(4),
                    ]),
            ]);
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
                    ->title('No in & out rows to save')
                    ->body('Enter in time or out time for at least one consultant.')
                    ->warning()
                    ->send();

                $this->isCreating = false;

                return;
            }

            $duplicateConsultantIds = InOut::query()
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
                    ->title('No new in & out entries were saved')
                    ->body($this->buildSkippedMessage($skippedConsultantNames->all(), $date))
                    ->warning()
                    ->send();

                return;
            }

            DB::transaction(function () use ($rowsToInsert, $date): void {
                foreach ($rowsToInsert as $row) {
                    InOut::create([
                        'date' => $date,
                        'consultant_id' => $row['consultant_id'],
                        'department_id' => $row['department_id'],
                        'in_time' => $this->normalizeTime($row['in_time'] ?? null),
                        'out_time' => $this->normalizeTime($row['out_time'] ?? null),
                    ]);
                }
            });
        } finally {
            $this->isCreating = false;
        }

        $notification = Notification::make()
            ->title($skippedConsultantNames->isNotEmpty() ? 'Bulk in & out partially created' : 'Bulk in & out created')
            ->body($this->buildSaveMessage($rowsToInsert->count(), $skippedConsultantNames->all(), $date));

        if ($skippedConsultantNames->isNotEmpty()) {
            $notification->warning();
        } else {
            $notification->success();
        }

        $notification->send();

        $this->redirect($this->getResourceUrl());
    }

    protected function getFormActions(): array
    {
        if (! (auth()->user()?->isSuperAdmin() ?? false)) {
            return [
                $this->getCancelFormAction(),
            ];
        }

        return [
            Action::make('create')
                ->label('Save Bulk In & Out')
                ->submit('create')
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return auth()->user()?->isSuperAdmin()
            ? 'Bulk Create In & Out'
            : 'Today Doctor In out Time';
    }

    public function getHeading(): string|Htmlable
    {
        return $this->getTitle();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return auth()->user()?->isSuperAdmin()
            ? 'Choose a date, enter in and out times for the consultants you need, and save them together.'
            : 'Press Doctor In or Doctor Out to save the current time instantly.';
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

    protected function getBulkRows(): array
    {
        $todayRecords = InOut::query()
            ->whereDate('date', Carbon::now()->toDateString())
            ->get()
            ->keyBy('consultant_id');

        return InOutResource::getAccessibleConsultantsQuery()
            ->with('department')
            ->get()
            ->map(function (Consultant $consultant) use ($todayRecords): array {
                $record = $todayRecords->get($consultant->id);

                return [
                    'consultant_id' => $consultant->id,
                    'consultant_name' => $consultant->name,
                    'department_id' => $consultant->department_id,
                    'department_name' => $consultant->department?->name,
                    'in_time' => $this->formatDisplayTime($record?->in_time),
                    'out_time' => $this->formatDisplayTime($record?->out_time),
                ];
            })
            ->all();
    }

    protected function getInOutFields(): array
    {
        if (auth()->user()?->isSuperAdmin()) {
            return [
                TimePicker::make('in_time')
                    ->label('In')
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat('h:i A')
                    ->format('h:i A'),
                TimePicker::make('out_time')
                    ->label('Out')
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat('h:i A')
                    ->format('h:i A'),
            ];
        }

        return [
            TextInput::make('in_time')
                ->label('In')
                ->readOnly()
                ->dehydrated(false)
                ->placeholder('-')
                ->suffixAction(
                    Action::make('doctor_in')
                        ->label('Doctor In')
                        ->icon('heroicon-m-arrow-down-circle')
                        ->action(function (Get $get, Set $set): void {
                            $time = $this->storeTime(
                                consultantId: (int) ($get('consultant_id') ?? 0),
                                departmentId: (int) ($get('department_id') ?? 0),
                                column: 'in_time',
                            );

                            if ($time) {
                                $set('in_time', $time);
                            }
                        })
                ),
            TextInput::make('out_time')
                ->label('Out')
                ->readOnly()
                ->dehydrated(false)
                ->placeholder('-')
                ->suffixAction(
                    Action::make('doctor_out')
                        ->label('Doctor Out')
                        ->icon('heroicon-m-arrow-up-circle')
                        ->action(function (Get $get, Set $set): void {
                            $time = $this->storeTime(
                                consultantId: (int) ($get('consultant_id') ?? 0),
                                departmentId: (int) ($get('department_id') ?? 0),
                                column: 'out_time',
                            );

                            if ($time) {
                                $set('out_time', $time);
                            }
                        })
                ),
        ];
    }

    protected function normalizeTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        foreach (['H:i:s', 'H:i', 'h:i A'] as $format) {
            try {
                return Carbon::createFromFormat($format, (string) $value)->format('H:i:s');
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return null;
    }

    protected function formatDisplayTime(mixed $value): ?string
    {
        $normalized = $this->normalizeTime($value);

        if (! $normalized) {
            return null;
        }

        return Carbon::createFromFormat('H:i:s', $normalized)->format('h:i A');
    }

    protected function storeTime(int $consultantId, int $departmentId, string $column): ?string
    {
        if (($consultantId <= 0) || ($departmentId <= 0) || ! in_array($column, ['in_time', 'out_time'], true)) {
            return null;
        }

        $now = Carbon::now();

        $record = InOut::query()->firstOrNew([
            'consultant_id' => $consultantId,
            'date' => $now->toDateString(),
        ]);

        if (filled($record->{$column})) {
            Notification::make()
                ->title($column === 'in_time' ? 'Doctor in time already saved' : 'Doctor out time already saved')
                ->warning()
                ->send();

            return $this->formatDisplayTime($record->{$column});
        }

        $record->department_id = $departmentId;
        $record->{$column} = $now->format('H:i:s');
        $record->save();

        Notification::make()
            ->title($column === 'in_time' ? 'Doctor in time saved' : 'Doctor out time saved')
            ->success()
            ->send();

        return $now->format('h:i A');
    }

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

    protected function buildSaveMessage(int $savedCount, array $skippedConsultantNames, string $date): string
    {
        $savedMessage = "{$savedCount} in & out entr" . ($savedCount === 1 ? 'y was' : 'ies were') . ' saved successfully.';

        if ($skippedConsultantNames === []) {
            return $savedMessage;
        }

        return $savedMessage . ' ' . $this->buildSkippedMessage($skippedConsultantNames, $date);
    }

    protected function buildSkippedMessage(array $skippedConsultantNames, string $date): string
    {
        $names = implode(', ', array_unique($skippedConsultantNames));

        return "Skipped existing consultants for {$date}: {$names}.";
    }

    protected function rowHasInput(array $row): bool
    {
        return filled($row['in_time'] ?? null) || filled($row['out_time'] ?? null);
    }
}
