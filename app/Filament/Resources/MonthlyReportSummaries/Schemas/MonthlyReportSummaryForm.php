<?php

namespace App\Filament\Resources\MonthlyReportSummaries\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class MonthlyReportSummaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Monthly Summary')
                    ->description('Set the Room, Consultant, and Occupied summary values for a specific month.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('year')
                            ->options(self::getYearOptions())
                            ->default((int) now()->format('Y'))
                            ->required(),
                        Select::make('month')
                            ->options(self::getMonthOptions())
                            ->default((int) now()->format('n'))
                            ->required()
                            ->rule(function ($get, $livewire) {
                                $recordId = $livewire->getRecord()?->id ?? null;

                                return Rule::unique('monthly_report_summaries', 'month')
                                    ->where(fn ($query) => $query->where('year', $get('year')))
                                    ->ignore($recordId);
                            })
                            ->validationMessages([
                                'unique' => 'This month summary already exists for the selected year.',
                            ]),
                        TextInput::make('room')
                            ->label('Room')
                            ->numeric()
                            ->default(46)
                            ->required(),
                        TextInput::make('consultant')
                            ->label('Consultant')
                            ->numeric()
                            ->default(106)
                            ->required(),
                        TextInput::make('occupied')
                            ->label('Occupied')
                            ->numeric()
                            ->default(30)
                            ->required(),
                    ]),
            ]);
    }

    protected static function getYearOptions(): array
    {
        $currentYear = (int) now()->format('Y');
        $years = range($currentYear - 2, $currentYear + 5);

        return collect($years)
            ->mapWithKeys(fn (int $year): array => [$year => $year])
            ->all();
    }

    protected static function getMonthOptions(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [
                $month => Carbon::createFromDate(2000, $month, 1)->format('F'),
            ])
            ->all();
    }
}
