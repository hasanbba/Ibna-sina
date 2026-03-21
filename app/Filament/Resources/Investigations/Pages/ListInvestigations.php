<?php

namespace App\Filament\Resources\Investigations\Pages;

use App\Filament\Resources\Investigations\InvestigationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvestigations extends ListRecords
{
    protected static string $resource = InvestigationResource::class;

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
