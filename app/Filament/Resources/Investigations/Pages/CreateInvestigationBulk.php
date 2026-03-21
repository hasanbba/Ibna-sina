<?php

namespace App\Filament\Resources\Investigations\Pages;

use App\Filament\Resources\Investigations\InvestigationResource;
use App\Models\Consultant;
use App\Models\Investigation;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateInvestigationBulk extends CreateRecord
{
    use InteractsWithForms;

    protected static string $resource = InvestigationResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $heading = 'Bulk Create Investigation';

    protected ?string $subheading = 'Choose a month, enter ID and amount for the consultants you need, and save them together.';

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill([
            'month' => Carbon::now()->startOfMonth()->toDateString(),
            'rows' => $this->getBulkRows(),
        ]);

        $this->callHook('afterFill');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Bulk Investigation')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('month')
                            ->label('Month')
                            ->required()
                            ->default(Carbon::now()->startOfMonth()->toDateString())
                            ->displayFormat('F Y')
                            ->native(false),
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
                                TextInput::make('investigation_id')
                                    ->label('ID')
                                    ->maxLength(255),
                                TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('Tk'),
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
            $month = $data['month'] ?? null;
            $rows = collect($data['rows'] ?? [])
                ->filter(fn (array $row): bool => $this->rowHasInput($row));

            if (blank($month)) {
                throw ValidationException::withMessages([
                    'data.month' => 'The month field is required.',
                ]);
            }

            if ($rows->isEmpty()) {
                Notification::make()
                    ->title('No investigation rows to save')
                    ->body('Enter ID or amount for at least one consultant.')
                    ->warning()
                    ->send();

                $this->isCreating = false;

                return;
            }

            $duplicateConsultantIds = Investigation::query()
                ->whereDate('month', $month)
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
                    ->title('No new investigation entries were saved')
                    ->body($this->buildSkippedMessage($skippedConsultantNames->all(), $month))
                    ->warning()
                    ->send();

                return;
            }

            DB::transaction(function () use ($rowsToInsert, $month): void {
                foreach ($rowsToInsert as $row) {
                    Investigation::create([
                        'month' => $month,
                        'consultant_id' => $row['consultant_id'],
                        'department_id' => $row['department_id'],
                        'investigation_id' => (string) ($row['investigation_id'] ?? ''),
                        'amount' => (float) ($row['amount'] ?? 0),
                    ]);
                }
            });
        } finally {
            $this->isCreating = false;
        }

        $notification = Notification::make()
            ->title($skippedConsultantNames->isNotEmpty() ? 'Bulk investigation partially created' : 'Bulk investigation created')
            ->body($this->buildSaveMessage($rowsToInsert->count(), $skippedConsultantNames->all(), $month));

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
        return [
            Action::make('create')
                ->label('Save Bulk Investigation')
                ->submit('create')
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Bulk Create Investigation';
    }

    protected function getBulkRows(): array
    {
        return InvestigationResource::getAccessibleConsultantsQuery()
            ->with('department')
            ->get()
            ->map(fn (Consultant $consultant): array => [
                'consultant_id' => $consultant->id,
                'consultant_name' => $consultant->name,
                'department_id' => $consultant->department_id,
                'department_name' => $consultant->department?->name,
                'investigation_id' => null,
                'amount' => null,
            ])
            ->all();
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

    protected function buildSaveMessage(int $savedCount, array $skippedConsultantNames, string $month): string
    {
        $savedMessage = "{$savedCount} investigation entr" . ($savedCount === 1 ? 'y was' : 'ies were') . ' saved successfully.';

        if ($skippedConsultantNames === []) {
            return $savedMessage;
        }

        return $savedMessage . ' ' . $this->buildSkippedMessage($skippedConsultantNames, $month);
    }

    protected function buildSkippedMessage(array $skippedConsultantNames, string $month): string
    {
        $names = implode(', ', array_unique($skippedConsultantNames));
        $formattedMonth = Carbon::parse($month)->format('F Y');

        return "Skipped existing consultants for {$formattedMonth}: {$names}.";
    }

    protected function rowHasInput(array $row): bool
    {
        return filled($row['investigation_id'] ?? null) || filled($row['amount'] ?? null);
    }
}
