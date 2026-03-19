<?php

namespace App\Filament\Resources\Reportings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('consultant.name'),
                TextColumn::make('department.name'),
                TextColumn::make('new'),
                TextColumn::make('report'),
                TextColumn::make('follow_up'),
                TextColumn::make('back'),
                TextColumn::make('total'),
                TextColumn::make('remark.name'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
