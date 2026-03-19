<?php

namespace App\Filament\Resources\Reportings\Pages;

use App\Filament\Resources\Reportings\ReportingResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReportings extends ListRecords
{
    protected static string $resource = ReportingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('report')
                ->label('Reports')
                ->icon('heroicon-o-chart-bar')
                ->url($this->getResourceUrl('report')),
            Action::make('bulkCreate')
                ->label('Bulk Create')
                ->icon('heroicon-o-squares-2x2')
                ->url($this->getResourceUrl('bulk-create')),
            CreateAction::make(),
        ];
    }
}
