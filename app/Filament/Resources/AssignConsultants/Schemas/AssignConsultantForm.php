<?php

namespace App\Filament\Resources\AssignConsultants\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssignConsultantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assignment Details')
                    ->schema([
                        Placeholder::make('user_name')
                            ->label('User')
                            ->content(fn ($record): string => $record?->name ?? '-'),
                        Placeholder::make('user_email')
                            ->label('Email')
                            ->content(fn ($record): string => $record?->email ?? '-'),
                        Select::make('consultants')
                            ->label('Consultants')
                            ->relationship('consultants', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->placeholder('Consultants')
                            ->helperText('This user will only be able to create and view reporting data for the selected consultants.'),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
