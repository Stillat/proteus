<?php

return [
    'stripe' => [
        'api_base' => 1,
        'dg_price_1' => 2,
        'dg_product_1' => 3,
        'dg_product_2' => 4,
        'public' => 5,
        'secret' => 6,
        'webhook' => 7,

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
