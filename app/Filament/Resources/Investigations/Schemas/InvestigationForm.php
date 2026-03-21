<?php

namespace App\Filament\Resources\Investigations\Schemas;

use App\Models\Consultant;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class InvestigationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Investigation')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('month')
                            ->label('Month')
                            ->required()
                            ->default(Carbon::now()->startOfMonth()->toDateString())
                            ->displayFormat('F Y')
                            ->native(false)
                            ->rule(function ($get, $livewire) {
                                $consultantId = $get('consultant_id');
                                $recordId = $livewire->getRecord()?->id ?? null;

                                return Rule::unique('investigations', 'month')
                                    ->where(fn ($query) => $query->where('consultant_id', $consultantId))
                                    ->ignore($recordId);
                            })
                            ->validationMessages([
                                'unique' => 'This data already exists.',
                            ]),
                        Select::make('consultant_id')
                            ->label('Consultant Name')
                            ->relationship('consultant', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state): void {
                                $set('department_id', Consultant::find($state)?->department_id);
                            })
                            ->required(),
                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->required(),
                        TextInput::make('investigation_id')
                            ->label('ID')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('amount')
                            ->numeric()
                            ->prefix('Tk')
                            ->required(),
                    ]),
            ]);
    }
}
