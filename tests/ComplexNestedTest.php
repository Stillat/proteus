<?php

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;

class ComplexNestedTest extends TestCase
{

    public function testThatMixedTypeValuesAreAddedWhenNesting()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/nested/001.php');
        $expected = file_get_contents(__DIR__ . '/expected/nested/001.php');
        $updater->update([
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

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

}