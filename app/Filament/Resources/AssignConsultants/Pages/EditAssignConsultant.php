<?php

namespace App\Filament\Resources\AssignConsultants\Pages;

use App\Filament\Resources\AssignConsultants\AssignConsultantsResource;
use Filament\Resources\Pages\EditRecord;

class EditAssignConsultant extends EditRecord
{
    protected static string $resource = AssignConsultantsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
