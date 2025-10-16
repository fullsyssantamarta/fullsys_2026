<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Services\Apidian\ApidianService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate tenant ID if not provided
        if (empty($data['id'])) {
            $data['id'] = Str::slug($data['nit']);
        }
        
        // Set trial end date if not set
        if (empty($data['trial_ends_at']) && $data['plan'] === 'trial') {
            $data['trial_ends_at'] = now()->addDays(30);
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        $tenant = $this->record;
        
        // Configure in APIDIAN automatically
        $this->configureApidian($tenant);
    }
    
    protected function configureApidian($tenant): void
    {
        try {
            // Prepare data for APIDIAN
            $apidianData = [
                'type_document_identification_id' => $tenant->type_document_identification_id ?? 3,
                'type_organization_id' => $tenant->type_organization_id ?? 2,
                'type_regime_id' => $tenant->type_regime_id ?? 2,
                'type_liability_id' => $tenant->type_liability_id ?? 14,
                'business_name' => $tenant->business_name ?? $tenant->name,
                'merchant_registration' => $tenant->merchant_registration ?? '0000000-00',
                'municipality_id' => $tenant->municipality_id ?? 820,
                'address' => $tenant->address ?? '',
                'phone' => $tenant->phone ?? '',
                'email' => $tenant->email,
            ];
            
            // Add mail configuration if provided
            if ($tenant->mail_host) {
                $apidianData['mail_host'] = $tenant->mail_host;
                $apidianData['mail_port'] = $tenant->mail_port;
                $apidianData['mail_username'] = $tenant->mail_username;
                $apidianData['mail_password'] = $tenant->mail_password;
                $apidianData['mail_encryption'] = $tenant->mail_encryption;
            }
            
            // Call APIDIAN API
            $apidianService = new ApidianService();
            $response = $apidianService->configureCompany(
                $tenant->nit,
                $tenant->dv ?? '0',
                $apidianData
            );
            
            if ($response['success']) {
                // Save APIDIAN token and response
                $tenant->update([
                    'apidian_token' => $response['data']['token'] ?? null,
                    'apidian_response' => $response['data'],
                    'apidian_configured_at' => now(),
                ]);
                
                Notification::make()
                    ->title('Empresa configurada en APIDIAN')
                    ->success()
                    ->body('La empresa se ha registrado correctamente en APIDIAN.')
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al configurar APIDIAN')
                    ->danger()
                    ->body('No se pudo configurar la empresa en APIDIAN: ' . ($response['error'] ?? 'Error desconocido'))
                    ->persistent()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al configurar APIDIAN')
                ->danger()
                ->body('Excepción: ' . $e->getMessage())
                ->persistent()
                ->send();
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
