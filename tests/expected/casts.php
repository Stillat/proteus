<?php

return [

    'debug' => (bool) env('APP_DEBUG', false),

    'nested' => [
        'test' => (bool) env('TEST', true)
    ],

    'test' => 40,
    'test2' => 60.3,
    'test3' => 'this is another string'
];
