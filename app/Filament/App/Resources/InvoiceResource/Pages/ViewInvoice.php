<?php

namespace App\Filament\App\Resources\InvoiceResource\Pages;

use App\Filament\App\Resources\InvoiceResource;
use App\Services\Apidian\ApidianService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
                
            // Enviar a DIAN
            Actions\Action::make('send_to_dian')
                ->label('Enviar a DIAN')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Enviar Factura a DIAN')
                ->modalDescription('¿Está seguro de enviar esta factura a la DIAN? Una vez enviada no podrá ser modificada.')
                ->action(function () {
                    try {
                        $apidian = app(ApidianService::class);
                        
                        // Calcular totales
                        $this->record->load('items');
                        $this->record->calculateTotals();
                        $this->record->save();
                        
                        // Enviar a APIDIAN
                        $response = $apidian->sendInvoice($this->record->toApidianFormat());
                        
                        // Actualizar registro
                        $this->record->update([
                            'status' => 'sent',
                            'cufe' => $response['cufe'] ?? null,
                            'qr_code' => $response['qr_code'] ?? null,
                            'zip_key' => $response['zip_key'] ?? null,
                            'dian_status' => $response['status'] ?? 'sent',
                            'dian_response' => $response,
                            'sent_to_dian_at' => now(),
                            'pdf_url' => $response['pdf_url'] ?? null,
                            'xml_url' => $response['xml_url'] ?? null,
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Factura enviada a DIAN')
                            ->body('La factura se ha enviado exitosamente.')
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Error al enviar factura')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
                
            // EVENTOS RADIAN
            Actions\ActionGroup::make([
                
                // 030 - Acuse de Recibo
                Actions\Action::make('radian_030')
                    ->label('Acuse de Recibo (030)')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn () => $this->record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Enviar Acuse de Recibo')
                    ->modalDescription('Confirma que se recibió la factura electrónica.')
                    ->action(function () {
                        $this->sendRadianEvent('030', 'Acuse de Recibo confirmado');
                    }),
                    
                // 032 - Aceptación Expresa
                Actions\Action::make('radian_032')
                    ->label('Aceptación Expresa (032)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn () => $this->record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Aceptación Expresa de la Factura')
                    ->modalDescription('Acepta expresamente la factura electrónica.')
                    ->action(function () {
                        $this->sendRadianEvent('032', 'Factura aceptada expresamente');
                    }),
                    
                // 033 - Aceptación Tácita
                Actions\Action::make('radian_033')
                    ->label('Aceptación Tácita (033)')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->visible(fn () => $this->record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Aceptación Tácita')
                    ->modalDescription('La factura se considera aceptada tácitamente.')
                    ->action(function () {
                        $this->sendRadianEvent('033', 'Factura aceptada tácitamente');
                    }),
                    
                // 034 - Rechazo
                Actions\Action::make('radian_034')
                    ->label('Rechazar Factura (034)')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => $this->record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Factura')
                    ->modalDescription('Rechaza la factura electrónica. Indique el motivo:')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Motivo del Rechazo')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data) {
                        $this->sendRadianEvent('034', $data['reason'] ?? 'Factura rechazada');
                    }),
                    
                // 035 - Reclamo
                Actions\Action::make('radian_035')
                    ->label('Reclamar Factura (035)')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn () => $this->record->status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Reclamar Factura')
                    ->modalDescription('Presenta un reclamo sobre la factura. Indique el motivo:')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Motivo del Reclamo')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data) {
                        $this->sendRadianEvent('035', $data['reason'] ?? 'Reclamo sobre factura');
                    }),
                    
            ])
            ->label('Eventos RADIAN')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->visible(fn () => $this->record->status === 'approved'),
            
            // Descargas
            Actions\ActionGroup::make([
                Actions\Action::make('download_pdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn () => !empty($this->record->pdf_url))
                    ->url(fn () => $this->record->pdf_url, shouldOpenInNewTab: true),
                    
                Actions\Action::make('download_xml')
                    ->label('Descargar XML')
                    ->icon('heroicon-o-code-bracket')
                    ->visible(fn () => !empty($this->record->xml_url))
                    ->url(fn () => $this->record->xml_url, shouldOpenInNewTab: true),
            ])
            ->label('Descargas')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->visible(fn () => !empty($this->record->pdf_url) || !empty($this->record->xml_url)),
        ];
    }
    
    /**
     * Enviar evento RADIAN
     */
    protected function sendRadianEvent(string $eventCode, string $description): void
    {
        try {
            $apidian = app(ApidianService::class);
            
            $eventData = [
                'event_code' => $eventCode,
                'invoice_number' => $this->record->full_number,
                'invoice_cufe' => $this->record->cufe,
                'description' => $description,
                'date' => now()->format('Y-m-d'),
                'time' => now()->format('H:i:s'),
            ];
            
            $response = $apidian->sendRadianEvent($eventData);
            
            // Actualizar la factura con la información del evento
            $dianResponse = $this->record->dian_response ?? [];
            $dianResponse['radian_events'][] = [
                'event_code' => $eventCode,
                'description' => $description,
                'sent_at' => now()->toIso8601String(),
                'response' => $response,
            ];
            
            $this->record->update([
                'dian_response' => $dianResponse,
            ]);
            
            Notification::make()
                ->success()
                ->title('Evento RADIAN Enviado')
                ->body("Evento {$eventCode} enviado exitosamente.")
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error al enviar evento RADIAN')
                ->body($e->getMessage())
                ->send();
        }
    }
}
