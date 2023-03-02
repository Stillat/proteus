<?php

return [

    'debug' => (bool) env('APP_DEBUG', true),
    'string' => (string) env('STRING_TEST', 'test'),
    'int' => (int) env('INT_TEST', '10'),
    'bool' => (bool) env('BOOL_TEST', '1'),
    'double' => (float) env('DOUBLE_TEST', '100.2'),
    'nested' => [
        'test' => (bool) env('TEST', false),
    ],

    'test' => 20,
    'test2' => 20.0,
    'test3' => 'this is string',
];
