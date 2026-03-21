<?php

namespace App\Filament\Resources\InOuts\Pages;

use App\Filament\Resources\InOuts\InOutResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInOut extends EditRecord
{
    protected static string $resource = InOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
