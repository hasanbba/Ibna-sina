<?php

namespace App\Filament\Resources\MonthlyReportSummaries\Pages;

use App\Filament\Resources\MonthlyReportSummaries\MonthlyReportSummaryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonthlyReportSummaries extends ListRecords
{
    protected static string $resource = MonthlyReportSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
