<?php

namespace App\Filament\Resources\Remarks\Pages;

use App\Filament\Resources\Remarks\RemarkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRemark extends EditRecord
{
    protected static string $resource = RemarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
