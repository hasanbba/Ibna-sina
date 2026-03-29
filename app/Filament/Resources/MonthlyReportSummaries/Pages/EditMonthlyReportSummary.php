<?php

namespace App\Filament\Resources\MonthlyReportSummaries\Pages;

use App\Filament\Resources\MonthlyReportSummaries\MonthlyReportSummaryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyReportSummary extends EditRecord
{
    protected static string $resource = MonthlyReportSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
