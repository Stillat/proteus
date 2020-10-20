<?php

return [

    'debug' => (bool) env('APP_DEBUG', true),

    'nested' => [
        'test' => (bool) env('TEST', false)
    ],

    'test' => 20,
    'test2' => 20.0,
    'test3' => 'this is string'
];
