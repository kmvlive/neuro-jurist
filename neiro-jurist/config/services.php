<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Timeweb AI Service Configuration
    |--------------------------------------------------------------------------
    */
    'timeweb_ai' => [
        'api_key' => env('TIMEWEB_AI_API_KEY'),
        'api_url' => env('TIMEWEB_AI_API_URL', 'https://api.timeweb.ai'),
    ],

    /*
    |--------------------------------------------------------------------------
    | T-Bank Payment Service Configuration
    |--------------------------------------------------------------------------
    */
    'tbank' => [
        'terminal_key' => env('T_BANK_TERMINAL_KEY'),
        'secret_key' => env('T_BANK_SECRET_KEY'),
        'payment_url' => env('T_BANK_PAYMENT_URL', 'https://rest-api-test.tinkoff.ru/api/v2'),
    ],

];
