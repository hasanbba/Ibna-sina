<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department Details')
                    ->description('Create or update the department name used across consultants and reporting.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Department Name')
                            ->placeholder('Enter department name')
                            ->maxLength(255)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
