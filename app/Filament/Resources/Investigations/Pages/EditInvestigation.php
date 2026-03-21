<?php

namespace App\Filament\Resources\Investigations\Pages;

use App\Filament\Resources\Investigations\InvestigationResource;
use App\Models\Investigation;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class EditInvestigation extends EditRecord
{
    protected static string $resource = InvestigationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Investigation
    {
        try {
            $record->update($data);

            return $record;
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw ValidationException::withMessages([
                    'data.month' => 'This data already exists.',
                ]);
            }

            throw $exception;
        }
    }
}
