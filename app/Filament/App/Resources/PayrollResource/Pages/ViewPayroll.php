<?php

namespace App\Filament\App\Resources\PayrollResource\Pages;

use App\Filament\App\Resources\PayrollResource;
use App\Services\Apidian\ApidianService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayroll extends ViewRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => $this->record->is_draft),

            Actions\Action::make('send_to_dian')
                ->label('Enviar a DIAN')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Enviar Nómina a DIAN')
                ->modalDescription('¿Está seguro que desea enviar esta nómina electrónica a la DIAN?')
                ->modalSubmitActionLabel('Sí, enviar')
                ->visible(fn() => $this->record->is_draft)
                ->action(function () {
                    try {
                        $apidian = app(ApidianService::class);
                        
                        // Convertir y enviar nómina a APIDIAN
                        $payrollData = $this->record->toApidianFormat();
                        $result = $apidian->sendPayroll($payrollData);
                        
                        if ($result['success']) {
                            $response = $result['data'];
                            
                            // Actualizar registro con respuesta de DIAN
                            $this->record->update([
                                'cune' => $response['cune'] ?? null,
                                'qr_code' => $response['QRStr'] ?? null,
                                'zip_key' => $response['zip_key'] ?? null,
                                'dian_status' => 'sent',
                                'dian_response' => $response,
                                'pdf_url' => $response['urlinvoicepdf'] ?? null,
                                'xml_url' => $response['urlinvoicexml'] ?? null,
                                'status' => 'sent',
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Nómina enviada')
                                ->body("CUNE: {$response['cune']}")
                                ->send();
                        } else {
                            throw new \Exception($result['error']['message'] ?? json_encode($result['error']));
                        }

                    } catch (\Exception $e) {
                        $this->record->update([
                            'dian_status' => 'rejected',
                            'dian_response' => ['error' => $e->getMessage()],
                            'status' => 'rejected',
                        ]);

                        Notification::make()
                            ->danger()
                            ->title('Error al enviar')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Actions\Action::make('download_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->visible(fn() => $this->record->pdf_url)
                ->url(fn() => $this->record->pdf_url, shouldOpenInNewTab: true),

            Actions\Action::make('download_xml')
                ->label('Descargar XML')
                ->icon('heroicon-o-code-bracket')
                ->color('gray')
                ->visible(fn() => $this->record->xml_url)
                ->url(fn() => $this->record->xml_url, shouldOpenInNewTab: true),

            Actions\DeleteAction::make()
                ->visible(fn() => $this->record->is_draft),
        ];
    }
}
