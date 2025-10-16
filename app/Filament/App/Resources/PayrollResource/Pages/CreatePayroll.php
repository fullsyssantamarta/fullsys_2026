<?php

namespace App\Filament\App\Resources\PayrollResource\Pages;

use App\Filament\App\Resources\PayrollResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Establecer valores por defecto
        $data['type_document_id'] = 102; // Nómina Individual
        $data['status'] = 'draft';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalcular totales después de crear
        $this->record->calculateTotals();
        $this->record->save();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
