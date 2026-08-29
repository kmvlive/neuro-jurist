<?php

return [

    'timeweb_ai' => [
        'api_key' => env('TIMEWEB_AI_API_KEY'),
        'api_url' => env('TIMEWEB_AI_API_URL', 'https://api.timeweb.ai'),
    ],

    'tbank' => [
        'terminal_key' => env('TINKOFF_TERMINAL_KEY'),
        'password' => env('TINKOFF_PASSWORD'),
        'payment_url' => env('TINKOFF_API_URL', 'https://securepay.tinkoff.ru/v2/'),
    ],

    'unisender' => [
        'api_key' => env('UNISENDER_API_KEY'),
        'from_email' => 'noreply@my.neiro-jurist.ru',
        'from_name' => env('UNISENDER_FROM_NAME', 'Нейро-юрист'),
    ],

    'admin_notify_email' => env('ADMIN_NOTIFY_EMAIL', 'admin@neiro-jurist.ru'),

];
