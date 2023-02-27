<?php

return [
    'stripe' => [
        'api_base' => env('STRIPE_API_BASE'),
        'dg_price_1' => env('STRIPE_PRICE_ONE'),
        'dg_product_1' => env('STRIPE_PRODUCT_ONE'),
        'dg_product_2' => env('STRIPE_PRODUCT_TWO'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook' => env('STRIPE_WEBHOOK_SECRET'),

        'reports' => [
            [
                'id' => 'eq48Qzwt',
                'frequency' => 'daily',
                'email_addresses' => 'something@example.org,another@example.org',
            ],
            [
                'id' => 'firstid',
                'frequency' => 'monthly',
                'email_addresses' => 'something@example.org,another@example.org',
            ],
        ],

        // Misc
        'query_params' => [
            'utm_source',
            'utm_medium',
            'utm_campaign',
        ],
    ],
];
