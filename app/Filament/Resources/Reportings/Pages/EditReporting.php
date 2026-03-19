<?php

namespace App\Filament\Resources\Reportings\Pages;

use App\Filament\Resources\Reportings\ReportingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReporting extends EditRecord
{
    protected static string $resource = ReportingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
