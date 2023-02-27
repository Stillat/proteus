<?php

return [

    'debug' => (bool) env('APP_DEBUG', false),
    'string' => (string) env('STRING_TEST', 'replace'),
    'int' => (int) env('INT_TEST', '20'),
    'bool' => (bool) env('BOOL_TEST', '0'),
    'double' => (float) env('DOUBLE_TEST', '40.2'),
    'nested' => [
        'test' => (bool) env('TEST', true),
    ],

    'test' => 40,
    'test2' => 60.3,
    'test3' => 'this is another string',
];
