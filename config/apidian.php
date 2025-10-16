<?php

return [
    /*
    |--------------------------------------------------------------------------
    | APIDIAN API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for APIDIAN electronic invoicing API integration
    |
    */

    'base_url' => env('APIDIAN_BASE_URL', 'https://api.apidian.com/api'),
    
    'token' => env('APIDIAN_TOKEN', ''),
    
    'environment' => env('APIDIAN_ENVIRONMENT', 'test'), // test or production
    
    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    */
    'timeout' => env('APIDIAN_TIMEOUT', 60),
    
    'connect_timeout' => env('APIDIAN_CONNECT_TIMEOUT', 10),
    
    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'times' => 3,
        'sleep' => 100, // milliseconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    */
    'document_types' => [
        'invoice' => 'factura-electronica',
        'credit_note' => 'nota-credito',
        'debit_note' => 'nota-debito',
        'payroll' => 'nomina-electronica',
        'pos' => 'pos-electronico',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'enabled' => env('APIDIAN_WEBHOOKS_ENABLED', false),
        'url' => env('APIDIAN_WEBHOOK_URL'),
        'secret' => env('APIDIAN_WEBHOOK_SECRET'),
    ],
];
