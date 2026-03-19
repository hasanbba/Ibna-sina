<?php

namespace App\Filament\Resources\Remarks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RemarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
            ]);
    }
}
