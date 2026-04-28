<?php

return [
    'api_key'        => env('GENIUSPAY_API_KEY', ''),
    'api_secret'     => env('GENIUSPAY_API_SECRET', ''),
    'webhook_secret' => env('GENIUSPAY_WEBHOOK_SECRET', ''),
    'base_url'       => env('GENIUSPAY_BASE_URL', 'https://pay.genius.ci/api/v1/merchant'),

    'plans' => [
        'monthly'  => ['amount' => 1000,  'label' => 'Mensuel',   'description' => 'Safio Premium — 1 mois'],
        'yearly'   => ['amount' => 9900,  'label' => 'Annuel',    'description' => 'Safio Premium — 1 an'],
        'lifetime' => ['amount' => 15000, 'label' => 'À vie',     'description' => 'Safio Premium — Accès à vie'],
    ],

    'success_url' => env('GENIUSPAY_SUCCESS_URL', 'https://safio.redirect/payment/success'),
    'error_url'   => env('GENIUSPAY_ERROR_URL',   'https://safio.redirect/payment/failed'),
];
