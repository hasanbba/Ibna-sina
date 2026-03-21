<?php

namespace App\Filament\Resources\Investigations\Pages;

use App\Filament\Resources\Investigations\InvestigationResource;
use App\Models\Investigation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class CreateInvestigation extends CreateRecord
{
    protected static string $resource = InvestigationResource::class;

    protected function handleRecordCreation(array $data): Investigation
    {
        try {
            return Investigation::create($data);
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
