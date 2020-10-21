<?php

include 'vendor/autoload.php';

$confWriter = new \Stillat\Proteus\ConfigUpdater();
$file = __DIR__.'/config.php';

$confWriter->open($file);

$confWriter->update([
    'test.new' => [
        'nested' => [
            'key-one' => 'value-one',
            'key-two' => 'value-two',
            'key-three' => 'value-three',
            'key-four' => [
                'nested-one' => 'nested-value-one',
                'nested-two' => 'nested-value-two',
                'nested-three' => [
                    'three' => 'value-three'
                ]
            ]
        ]
    ]
]);

echo $confWriter->getDocument();
