<?php

return [
    'api_keys' => [
        'secret_key' => env('STRIPE_SECRET_KEY', null),
        'publishable_key' => env('STRIPE_API_KEYS_PUBLISHABLE_KEY',null)
    ]
];


