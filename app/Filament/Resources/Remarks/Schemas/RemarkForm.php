<?php

namespace App\Filament\Resources\Remarks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RemarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Remark Details')
                    ->description('Create or update the remark options used in reporting records.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Remark Name')
                            ->placeholder('Enter remark name')
                            ->maxLength(255)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
