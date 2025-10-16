<?php

namespace App\Services\Apidian;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ApidianService
{
    protected string $baseUrl;
    protected string $token;
    protected int $timeout;
    protected int $connectTimeout;
    
    public function __construct()
    {
        $this->baseUrl = config('apidian.base_url');
        $this->token = config('apidian.token');
        $this->timeout = config('apidian.timeout');
        $this->connectTimeout = config('apidian.connect_timeout');
    }
    
    /**
     * Create HTTP client with default headers
     */
    protected function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->connectTimeout($this->connectTimeout);
    }
    
    /**
     * Send electronic invoice
     */
    public function sendInvoice(array $data)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/ubl2.1/invoice", $data);
            
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
            Log::error('APIDIAN Invoice Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send credit note
     */
    public function sendCreditNote(array $data)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/ubl2.1/credit-note", $data);
            
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
            Log::error('APIDIAN Credit Note Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send debit note
     */
    public function sendDebitNote(array $data)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/ubl2.1/debit-note", $data);
            
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
            Log::error('APIDIAN Debit Note Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check document status
     */
    public function checkStatus(string $documentKey)
    {
        try {
            $response = $this->client()
                ->get("{$this->baseUrl}/status/{$documentKey}");
            
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
            Log::error('APIDIAN Status Check Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Download PDF
     */
    public function downloadPdf(string $documentKey)
    {
        try {
            $response = $this->client()
                ->get("{$this->baseUrl}/pdf/{$documentKey}");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'content' => $response->body(),
                    'content_type' => $response->header('Content-Type'),
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('APIDIAN PDF Download Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send electronic payroll
     */
    public function sendPayroll(array $data)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/ubl2.1/payroll", $data);
            
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
            Log::error('APIDIAN Payroll Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send support document (Documento Soporte)
     */
    public function sendSupportDocument(array $data)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/ubl2.1/support-document", $data);
            
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
            Log::error('APIDIAN Support Document Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send RADIAN event (Acuse de recibo, Aceptación, Rechazo, etc.)
     * 
     * Event types:
     * - 030: Acuse de recibo
     * - 032: Aceptación expresa
     * - 033: Aceptación tácita
     * - 034: Rechazo
     * - 035: Reclamo
     */
    public function sendRadianEvent(array $data)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/ubl2.1/radian", $data);
            
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
            Log::error('APIDIAN RADIAN Event Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send payroll adjustment note (Nota de Ajuste de Nómina)
     */
    public function sendPayrollAdjustment(array $data)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/ubl2.1/payroll-adjustment", $data);
            
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
            Log::error('APIDIAN Payroll Adjustment Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Download attached document (XML)
     */
    public function downloadXml(string $documentKey)
    {
        try {
            $response = $this->client()
                ->get("{$this->baseUrl}/xml/{$documentKey}");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'content' => $response->body(),
                    'content_type' => $response->header('Content-Type'),
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->json(),
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('APIDIAN XML Download Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send email with document
     */
    public function sendEmail(string $documentKey, array $emailData)
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/send-email/{$documentKey}", $emailData);
            
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
            Log::error('APIDIAN Send Email Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get numbering resolution
     */
    public function getNumberingResolution(string $nit)
    {
        try {
            $response = $this->client()
                ->get("{$this->baseUrl}/numbering-resolution/{$nit}");
            
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
            Log::error('APIDIAN Get Numbering Resolution Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Configure company in APIDIAN
     */
    public function configureCompany(string $nit, string $dv, array $data)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->post("{$this->baseUrl}/ubl2.1/config/{$nit}/{$dv}", $data);
            
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
            Log::error('APIDIAN Company Config Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
