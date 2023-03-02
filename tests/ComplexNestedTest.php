<?php

namespace Stillat\Proteus\Tests;

class ComplexNestedTest extends ProteusTestCase
{
    public function testThatMixedTypeValuesAreAddedWhenNesting()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/nested/001.php',
            __DIR__.'/expected/nested/001.php',
            [
                'test' => [
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
            ]
        );
    }

    public function testThatNonArrayValuesUseLastKeyElementForNewElement()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/nested/002.php',
            __DIR__.'/expected/nested/002.php',
            [
                'test' => [
                    'nested' => [
                        'type' => 1,
                    ],
                ],
            ]
        );
    }

    public function testThatDotNotationKeysAreNotExpandedWhenUsedInsideArrays()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/nested/003.php',
            __DIR__.'/expected/nested/003.php',
            [
                'nested' => [
                    'new' => [
                        'key' => [
                            'these.keys' => 'should not get expanded',
                        ],
                    ],
                ],
            ]
        );
    }

    public function testThatComplexNestedKeysGetValuesReplaced()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/nested/004.php',
            __DIR__.'/expected/nested/004.php',
            [
                'nested.new.key' => [
                    'these.keys' => 'replacement value',
                ],
            ]
        );
    }

    public function testThatComplexReplacementsAllowAddingNewElements()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/nested/004.php',
            __DIR__.'/expected/nested/005.php',
            [
                'nested.new.key' => [
                    'these.keys' => 'replacement value',
                    'this' => 'should be added',
                ],
            ]
        );
    }
}
