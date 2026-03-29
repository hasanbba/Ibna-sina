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
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('consultant.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('department.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('new')
                    ->searchable(),
                TextColumn::make('report')
                    ->searchable(),
                TextColumn::make('follow_up')
                    ->searchable(),
                TextColumn::make('back')
                    ->searchable(),
                TextColumn::make('total')
                    ->searchable(),
                TextColumn::make('remark.name')
                    ->searchable(),
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
