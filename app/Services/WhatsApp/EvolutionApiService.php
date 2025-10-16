<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EvolutionApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instance;
    
    public function __construct()
    {
        $this->baseUrl = config('services.evolution_api.base_url');
        $this->apiKey = config('services.evolution_api.api_key');
        $this->instance = config('services.evolution_api.instance');
    }
    
    /**
     * Create HTTP client with default headers
     */
    protected function client()
    {
        return Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout(30);
    }
    
    /**
     * Send text message
     */
    public function sendText(string $number, string $message)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/message/sendText/{$this->instance}", [
                    'number' => $number,
                    'text' => $message,
                ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp Send Text Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send media (PDF, images, etc.)
     */
    public function sendMedia(string $number, string $mediaUrl, string $caption = '')
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/message/sendMedia/{$this->instance}", [
                    'number' => $number,
                    'mediatype' => 'document',
                    'media' => $mediaUrl,
                    'caption' => $caption,
                ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp Send Media Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send invoice via WhatsApp
     */
    public function sendInvoice(string $number, string $pdfUrl, string $invoiceNumber)
    {
        $message = "Hola! Tu factura electrónica #{$invoiceNumber} está lista.";
        
        return $this->sendMedia($number, $pdfUrl, $message);
    }
    
    /**
     * Check instance status
     */
    public function checkStatus()
    {
        try {
            $response = $this->client()
                ->get("{$this->baseUrl}/instance/connectionState/{$this->instance}");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp Status Check Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
