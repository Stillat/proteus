<?php

return [
    'trackable_videos' => env('SIMPLE_TRACKABLE_VIDEOS', false),

    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'webhook' => env('STRIPE_WEBHOOK_SECRET'),

        // Reporting
        'reports' => [
            [
                'frequency' => 'daily',
                'email_addresses' => 'entry1',
            ],
            [
                'frequency' => 'daily',
                'email_addresses' => 'entry2',
            ],
            [
                'frequency' => 'daily',
                'email_addresses' => 'entry3',
            ],
        ],
        'leave' => 'this should be ignored.',
        'test' => [
            'what' => [
                'nested' => 'value',
                'happens' => [
                    1,
                    2,
                    3,
                    'four',
                    'five',
                    5,
                    'six',
                    'seven' => [
                        8,
                    ],
                ],
            ],
        ],
    ],

];
