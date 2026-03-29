<?php

namespace App\Filament\Resources\Consultants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConsultantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Consultant Name')
                    ->searchable(),
                TextColumn::make('designation')
                    ->label('Designation')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('department.name')->label('Department'),
                TextColumn::make('chamber_time')
                    ->label('Chamber Time')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('status')
                ->colors([
                    'success' => 'active',
                    'danger' => 'inactive',
                ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
