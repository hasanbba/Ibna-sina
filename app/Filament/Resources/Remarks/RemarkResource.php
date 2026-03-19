<?php

namespace App\Filament\Resources\Remarks;

use App\Filament\Resources\Remarks\Pages\CreateRemark;
use App\Filament\Resources\Remarks\Pages\EditRemark;
use App\Filament\Resources\Remarks\Pages\ListRemarks;
use App\Filament\Resources\Remarks\Schemas\RemarkForm;
use App\Filament\Resources\Remarks\Tables\RemarksTable;
use App\Models\Remark;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RemarkResource extends Resource
{
    protected static ?string $model = Remark::class;
    protected static string|BackedEnum|null $navigationIcon = "fas-user-secret";
    protected static ?string $recordTitleAttribute = 'Remark';
    protected static string | UnitEnum | null $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return RemarkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RemarksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRemarks::route('/'),
            'create' => CreateRemark::route('/create'),
            'edit' => EditRemark::route('/{record}/edit'),
        ];
    }
}
