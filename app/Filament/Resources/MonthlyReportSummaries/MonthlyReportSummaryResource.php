<?php

namespace App\Filament\Resources\MonthlyReportSummaries;

use App\Filament\Resources\MonthlyReportSummaries\Pages\CreateMonthlyReportSummary;
use App\Filament\Resources\MonthlyReportSummaries\Pages\EditMonthlyReportSummary;
use App\Filament\Resources\MonthlyReportSummaries\Pages\ListMonthlyReportSummaries;
use App\Filament\Resources\MonthlyReportSummaries\Schemas\MonthlyReportSummaryForm;
use App\Filament\Resources\MonthlyReportSummaries\Tables\MonthlyReportSummariesTable;
use App\Models\MonthlyReportSummary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MonthlyReportSummaryResource extends Resource
{
    protected static ?string $model = MonthlyReportSummary::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Consultant Management';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Monthly Report Summary';

    public static function form(Schema $schema): Schema
    {
        return MonthlyReportSummaryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MonthlyReportSummariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyReportSummaries::route('/'),
            'create' => CreateMonthlyReportSummary::route('/create'),
            'edit' => EditMonthlyReportSummary::route('/{record}/edit'),
        ];
    }
}
