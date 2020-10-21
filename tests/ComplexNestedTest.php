<?php

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;

class ComplexNestedTest extends TestCase
{

    public function testThatMixedTypeValuesAreAddedWhenNesting()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/nested/001.php');
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

    public function testThatNonArrayValuesUseLastKeyElementForNewElement()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/nested/002.php');
        $expected = file_get_contents(__DIR__ . '/expected/nested/002.php');
        $updater->update([
            'test.nested.type' => 1
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatDotNotationKeysAreNotExpandedWhenUsedInsideArrays()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/nested/003.php');
        $expected = file_get_contents(__DIR__ . '/expected/nested/003.php');
        $updater->update([
            'nested.new.key' => [
                'these.keys' => 'should not get expanded'
            ]
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatComplexNestedKeysGetValuesReplaced()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/nested/004.php');
        $expected = file_get_contents(__DIR__ . '/expected/nested/004.php');
        $updater->update([
            'nested.new.key' => [
                'these.keys' => 'replacement value'
            ]
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatComplexReplacementsAllowAddingNewElements()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/nested/004.php');
        $expected = file_get_contents(__DIR__ . '/expected/nested/005.php');
        $updater->update([
            'nested.new.key' => [
                'these.keys' => 'replacement value',
                'this' => 'should be added'
            ]
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

}