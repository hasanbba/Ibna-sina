<?php

namespace App\Filament\Resources\Reportings\Pages;

use App\Filament\Resources\Reportings\ReportingResource;
use Filament\Pages\Dashboard;
use Filament\Resources\Pages\CreateRecord;

class CreateReporting extends CreateRecord
{
    protected static string $resource = ReportingResource::class;

    protected function getRedirectUrl(): string
    {
        if (! (auth()->user()?->isSuperAdmin() ?? false)) {
            return Dashboard::getUrl(panel: 'sysadmin');
        }

        return static::getResource()::getUrl();
    }
}
