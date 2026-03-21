<?php

namespace App\Filament\Resources\InOuts;

use App\Filament\Resources\InOuts\Pages\CreateInOut;
use App\Filament\Resources\InOuts\Pages\CreateInOutBulk;
use App\Filament\Resources\InOuts\Pages\EditInOut;
use App\Filament\Resources\InOuts\Pages\ListInOuts;
use App\Filament\Resources\InOuts\Schemas\InOutForm;
use App\Filament\Resources\InOuts\Tables\InOutsTable;
use App\Models\Consultant;
use App\Models\InOut;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InOutResource extends Resource
{
    protected static ?string $model = InOut::class;

    protected static ?string $recordTitleAttribute = 'date';

    protected static ?string $navigationLabel = 'In & Out';

    protected static string|BackedEnum|null $navigationIcon = 'fas-right-left';

    protected static string | UnitEnum | null $navigationGroup = 'Consultant Management';

    protected static ?int $navigationSort = 7;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return InOutForm::configure($schema);
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
        return InOutsTable::configure($table);
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

    public static function getPages(): array
    {
        return [
            'index' => ListInOuts::route('/'),
            'create' => CreateInOut::route('/create'),
            'bulk-create' => CreateInOutBulk::route('/bulk-create'),
            'edit' => EditInOut::route('/{record}/edit'),
        ];
    }
}
