<?php

namespace App\Filament\App\Resources\CreditNoteResource\Pages;

use App\Filament\App\Resources\CreditNoteResource;
use App\Services\Apidian\ApidianService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCreditNote extends ViewRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => $this->record->is_draft),

            // Enviar a DIAN
            Actions\Action::make('send_to_dian')
                ->label('Enviar a DIAN')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn() => $this->record->is_draft)
                ->requiresConfirmation()
                ->modalHeading('Enviar Nota Crédito a DIAN')
                ->modalDescription('¿Está seguro de enviar esta nota crédito a la DIAN? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, enviar')
                ->action(function () {
                    try {
                        $apidian = new ApidianService();
                        $response = $apidian->sendCreditNote($this->record->toApidianFormat());

                        if ($response['success']) {
                            // Actualizar registro con respuesta DIAN
                            $this->record->update([
                                'cude' => $response['data']['cude'] ?? null,
                                'qr_code' => $response['data']['QRStr'] ?? null,
                                'zip_key' => $response['data']['zip_key'] ?? null,
                                'dian_status' => 'approved',
                                'dian_response' => $response,
                                'sent_to_dian_at' => now(),
                                'pdf_url' => $response['data']['urlinvoicepdf'] ?? null,
                                'xml_url' => $response['data']['urlinvoicexml'] ?? null,
                                'status' => 'sent',
                            ]);

                            Notification::make()
                                ->title('Nota Crédito enviada exitosamente')
                                ->success()
                                ->body('CUDE: ' . ($response['data']['cude'] ?? 'N/A'))
                                ->send();
                        } else {
                            $this->record->update([
                                'dian_status' => 'rejected',
                                'dian_response' => $response,
                            ]);

                            Notification::make()
                                ->title('Error al enviar Nota Crédito')
                                ->danger()
                                ->body($response['message'] ?? 'Error desconocido')
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->danger()
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            // Descargar PDF
            Actions\Action::make('download_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->visible(fn() => !empty($this->record->pdf_url))
                ->url(fn() => $this->record->pdf_url, true),

            // Descargar XML
            Actions\Action::make('download_xml')
                ->label('Descargar XML')
                ->icon('heroicon-o-code-bracket')
                ->color('gray')
                ->visible(fn() => !empty($this->record->xml_url))
                ->url(fn() => $this->record->xml_url, true),
        ];
    }
}
