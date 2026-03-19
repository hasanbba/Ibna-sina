<?php

namespace App\Filament\Resources\AssignConsultants;

use App\Filament\Resources\AssignConsultants\Pages\EditAssignConsultant;
use App\Filament\Resources\AssignConsultants\Pages\ListAssignConsultants;
use App\Filament\Resources\AssignConsultants\Schemas\AssignConsultantForm;
use App\Filament\Resources\AssignConsultants\Tables\AssignConsultantsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AssignConsultantsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'fas-user-group';

    protected static ?string $navigationLabel = 'Assign Consultants';

    protected static string | UnitEnum | null $navigationGroup = 'Admin Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereDoesntHave('roles', fn (Builder $query): Builder => $query->where('name', 'super_admin'));
    }

    public static function form(Schema $schema): Schema
    {
        return AssignConsultantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignConsultantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignConsultants::route('/'),
            'edit' => EditAssignConsultant::route('/{record}/edit'),
        ];
    }
}
