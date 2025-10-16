<?php

namespace App\Filament\App\Resources\CreditNoteResource\Pages;

use App\Filament\App\Resources\CreditNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCreditNote extends CreateRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type_document_id'] = 91; // Nota Crédito
        $data['status'] = 'draft';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalcular totales después de crear
        $this->record->load('items');
        $this->record->calculateTotals();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
