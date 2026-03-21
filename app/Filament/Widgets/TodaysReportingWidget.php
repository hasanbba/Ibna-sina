<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\InOuts\InOutResource;
use App\Filament\Resources\Reportings\ReportingResource;
use Filament\Widgets\Widget;

class TodaysReportingWidget extends Widget
{
    protected string $view = 'filament.widgets.todays-reporting-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && ! auth()->user()?->isSuperAdmin();
    }

    public function getViewData(): array
    {
        $isSuperAdmin = auth()->user()?->isSuperAdmin();

        return [
            'reportingUrl' => ReportingResource::getUrl('bulk-create'),
            'title' => $isSuperAdmin ? 'Quick Actions' : 'Daily Entry',
            'reportingLabel' => $isSuperAdmin ? 'Bulk Create Reporting' : "Today's Reporting",
            'inOutUrl' => InOutResource::getUrl('bulk-create'),
            'inOutLabel' => $isSuperAdmin ? 'Bulk Create In & Out' : 'Today Doctor In out Time',
            'description' => $isSuperAdmin
                ? 'Open the bulk create pages for reporting and in & out.'
                : 'Open today\'s reporting form and today doctor in out time.',
        ];
    }
}
