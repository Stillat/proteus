<?php

return [

    'debug' => (bool) env('APP_DEBUG', true),
    'string' => (string) env('STRING_TEST', 'test'),
    'nested' => [
        'test' => (bool) env('TEST', false)
    ],

    'test' => 20,
    'test2' => 20.0,
    'test3' => 'this is string'
];
