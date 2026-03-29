<?php

namespace App\Filament\Resources\Consultants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\department;

class ConsultantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Consultant Details')
                    ->description('Manage consultant identity, designation, and chamber schedule.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->options(Department::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Consultant Name')
                            ->placeholder('Enter consultant name')
                            ->required(),
                        TextInput::make('designation')
                            ->label('Designation')
                            ->placeholder('Enter designation')
                            ->maxLength(255),
                        TextInput::make('chamber_time')
                            ->label('Chamber Time')
                            ->placeholder('Example: 7PM-10PM')
                            ->helperText('Use a simple range like 7PM-10PM.')
                            ->maxLength(255),
                        Select::make('status')
                            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }
}
