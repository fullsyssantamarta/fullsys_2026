<?php

namespace App\Filament\App\Resources\DebitNoteResource\Pages;

use App\Filament\App\Resources\DebitNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDebitNote extends CreateRecord
{
    protected static string $resource = DebitNoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type_document_id'] = 92; // Nota Crédito
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
