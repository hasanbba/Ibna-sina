<?php

namespace App\Filament\Resources\Reportings\Schemas;

use App\Filament\Resources\Reportings\ReportingResource;
use App\Models\Consultant;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\Rule;

class ReportingForm
{
    public static function configure(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Section::make('Reporting')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                DatePicker::make('date')
                    ->required()
                    ->default(Carbon::now()->toDateString())
                    ->rule(function ($get, $livewire) {
                        $consultantId = $get('consultant_id');
                        $recordId = $livewire->getRecord()?->id ?? null;

                        return Rule::unique('reportings', 'date')
                            ->where(fn ($query) => $query->where('consultant_id', $consultantId))
                            ->ignore($recordId);
                    }),
                Select::make('consultant_id')
                    ->options(fn (): array => ReportingResource::getAccessibleConsultantOptions())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $set('department_id', optional(Consultant::find($state))->department_id);

                        // Auto-fill date when selecting a consultant (so each consultant can have its own date)
                        if (! $get('date')) {
                            $set('date', Carbon::now()->toDateString());
                        }
                    }),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                self::makeNumberInput('new'),
                self::makeNumberInput('report'),
                self::makeNumberInput('follow_up'),
                self::makeNumberInput('back'),
                TextInput::make('total')
                    ->integer()
                    ->disabled()
                    ->dehydrated(false),
                Select::make('remark_id')
                    ->relationship('remark', 'name')
                    ->placeholder('Remark'),
                    ]),
            ]);
    }

    protected static function makeNumberInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->inputMode('decimal')
            ->default(0)
            ->autocomplete(false)
            ->extraInputAttributes([
                'x-on:focus' => '$el.select()',
            ])
            ->afterStateUpdatedJs(self::getTotalUpdateJs());
    }

    protected static function getTotalUpdateJs(): string
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
}
