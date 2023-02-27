<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */
    'timezone' => 'UTC', 'test' => [
        'new' => [
            'nested' => [
                'key-one' => 'value-one',
                'key-two' => 'value-two',
                'key-three' => 'value-three',
                'key-four' => [
                    'nested-one' => 'nested-value-one',
                    'nested-two' => 'nested-value-two',
                    'nested-three' => [
                        'three' => 'value-three',
                    ],
                ],
            ],
        ],
    ],

];
