<?php

namespace App\Filament\Resources\InOuts\Schemas;

use App\Filament\Resources\InOuts\InOutResource;
use App\Models\Consultant;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class InOutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('In & Out')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('consultant_id')
                            ->label('Consultant Name')
                            ->options(fn (): array => InOutResource::getAccessibleConsultantsQuery()->pluck('name', 'id')->all())
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
                        DatePicker::make('date')
                            ->required()
                            ->default(Carbon::now()->toDateString())
                            ->rule(function ($get, $livewire) {
                                $consultantId = $get('consultant_id');
                                $recordId = $livewire->getRecord()?->id ?? null;

                                return Rule::unique('in_outs', 'date')
                                    ->where(fn ($query) => $query->where('consultant_id', $consultantId))
                                    ->ignore($recordId);
                            })
                            ->validationMessages([
                                'unique' => 'This data already exists.',
                            ]),
                        TimePicker::make('in_time')
                            ->label('In')
                            ->seconds(false)
                            ->native(false)
                            ->format('h:i A')
                            ->required(),
                        TimePicker::make('out_time')
                            ->label('Out')
                            ->seconds(false)
                            ->native(false)
                            ->format('h:i A')
                            ->nullable(),
                    ]),
            ]);
    }
}
