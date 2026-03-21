<?php

namespace App\Filament\Resources\Investigations;

use App\Filament\Resources\Investigations\Pages\CreateInvestigation;
use App\Filament\Resources\Investigations\Pages\CreateInvestigationBulk;
use App\Filament\Resources\Investigations\Pages\EditInvestigation;
use App\Filament\Resources\Investigations\Pages\ListInvestigations;
use App\Filament\Resources\Investigations\Schemas\InvestigationForm;
use App\Filament\Resources\Investigations\Tables\InvestigationsTable;
use App\Models\Consultant;
use App\Models\Investigation;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestigationResource extends Resource
{
    protected static ?string $model = Investigation::class;

    protected static ?string $recordTitleAttribute = 'investigation_id';

    protected static string|BackedEnum|null $navigationIcon = 'fas-flask';

    protected static string | UnitEnum | null $navigationGroup = 'Consultant Management';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return InvestigationForm::configure($schema);
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
        return InvestigationsTable::configure($table);
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
            'index' => ListInvestigations::route('/'),
            'create' => CreateInvestigation::route('/create'),
            'bulk-create' => CreateInvestigationBulk::route('/bulk-create'),
            'edit' => EditInvestigation::route('/{record}/edit'),
        ];
    }
}
