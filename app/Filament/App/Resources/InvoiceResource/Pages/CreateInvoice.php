<?php

namespace App\Filament\App\Resources\InvoiceResource\Pages;

use App\Filament\App\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Establecer valores por defecto
        $data['type_document_id'] = 1; // Factura electrónica
        $data['status'] = 'draft'; // Borrador por defecto
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Calcular totales automáticamente después de crear
        $this->record->load('items');
        $this->record->calculateTotals();
        $this->record->save();
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
