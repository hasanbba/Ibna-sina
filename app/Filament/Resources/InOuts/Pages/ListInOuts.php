<?php

namespace App\Filament\Resources\InOuts\Pages;

use App\Filament\Resources\InOuts\InOutResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInOuts extends ListRecords
{
    protected static string $resource = InOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkCreate')
                ->label('Bulk Create')
                ->icon('heroicon-o-squares-2x2')
                ->url($this->getResourceUrl('bulk-create')),
            CreateAction::make(),
        ];
    }
}
