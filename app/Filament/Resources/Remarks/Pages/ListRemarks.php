<?php

namespace App\Filament\Resources\Remarks\Pages;

use App\Filament\Resources\Remarks\RemarkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRemarks extends ListRecords
{
    protected static string $resource = RemarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
