<?php

namespace App\Filament\Resources\Reportings;

use App\Filament\Resources\Reportings\Pages\CreateReporting;
use App\Filament\Resources\Reportings\Pages\CreateReportingBulk;
use App\Filament\Resources\Reportings\Pages\EditReporting;
use App\Filament\Resources\Reportings\Pages\ListReportings;
use App\Filament\Resources\Reportings\Pages\ReportingReport;
use App\Filament\Resources\Reportings\Schemas\ReportingForm;
use App\Filament\Resources\Reportings\Tables\ReportingsTable;
use App\Models\Consultant;
use App\Models\Reporting;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportingResource extends Resource
{
    protected static ?string $model = Reporting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string | UnitEnum | null $navigationGroup = 'Consultant Management';

    protected static ?string $recordTitleAttribute = 'Reporting';

    public static function form(Schema $schema): Schema
    {
        return ReportingForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user instanceof User || $user->isSuperAdmin()) {
            return $query;
        }

        $consultantIds = $user->assignedConsultantIds();

        if ($consultantIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('consultant_id', $consultantIds);
    }

    public static function table(Table $table): Table
    {
        return ReportingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getAccessibleConsultantsQuery(): Builder
    {
        $query = Consultant::query()->orderBy('name');
        $user = auth()->user();

        if (! $user instanceof User || $user->isSuperAdmin()) {
            return $query;
        }

        $consultantIds = $user->assignedConsultantIds();

        if ($consultantIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $consultantIds);
    }

    public static function getAccessibleConsultantOptions(): array
    {
        return static::getAccessibleConsultantsQuery()
            ->pluck('name', 'id')
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportings::route('/'),
            'report' => ReportingReport::route('/report'),
            'create' => CreateReporting::route('/create'),
            'bulk-create' => CreateReportingBulk::route('/bulk-create'),
            'edit' => EditReporting::route('/{record}/edit'),
        ];
    }
}
