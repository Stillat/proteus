<?php

namespace Stillat\Proteus\Tests;

class NewRootEntriesAreAddedTest extends ProteusTestCase
{
    public function testThatNewRootArrayEntriesAreAdded()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/simplenewkey.php',
            [
                'new' => [
                    'value-one',
                    'value-two',
                ],
            ]
        );
    }

    public function testThatNewSimpleKeysProperlyNested()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/simpledotnotationkeyset.php',
            [
                'new' => [
                    'key' => 'value',
                ],
            ]
        );
    }

    public function testThatNewDeeplyNestedKeysAreCreated()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/deeplynestedtest.php',
            [
                'new' => [
                    'deeply' => [
                        'nested' => [
                            'key' => [
                                'hello',
                                'world',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    public function testThatNewSimpleItemsAreAppendedToArrays()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/appendarray.php',
            __DIR__.'/expected/appendarray.php',
            [
                'test' => 'new-value',
            ]
        );
    }

    public function testThatResettingAnArrayReplacesTheArray()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/appendarray.php',
            __DIR__.'/expected/arrayreplacesarray.php',
            [
                'test' => ['new-value'],
            ]
        );
    }

    public function testThatNullValuesAreProperlyConvertedAndSaved()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/issues/004.php',
            __DIR__.'/expected/issues/004.php',
            [
                'forms' => [
                    'another_form' => [
                        'name_field' => 'name3',
                        'first_name_field' => 'first_name',
                        'last_name_field' => 'last_name',
                        'email_field' => 'email3',
                        'content_field' => 'message',
                        'handle' => 'contact_you',
                        'consent_field' => null,
                    ],
                ],
            ]
        );
    }

    public function testThatNewNestedEntriesAreProperlyCreated()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/issues/001/setnew.php',
            __DIR__.'/expected/issues/001/setnew.php',
            [
                'forms' => [
                    'contact_us' => [
                        'name_field' => 'name',
                        'first_name_field' => 'first_name',
                        'last_name_field' => 'last_name',
                        'email_field' => 'email',
                        'content_field' => 'message',
                        'handle' => 'contact_us',
                    ],
                    'contact_you' => [
                        'name_field' => 'name3',
                        'first_name_field' => 'first_name',
                        'last_name_field' => 'last_name',
                        'email_field' => 'email3',
                        'content_field' => 'message',
                        'handle' => 'contact_you',
                    ],
                ],
            ]
        );
    }

    public function testThatNewNestedEntriesAreProperlyUpdatedOnExistingPlaceholder()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/issues/001/fromplaceholder.php',
            __DIR__.'/expected/issues/001/fromplaceholder.php',
            [
                'forms' => [
                    'contact_us' => [
                        'name_field' => 'name',
                        'first_name_field' => 'first_name',
                        'last_name_field' => 'last_name',
                        'email_field' => 'email',
                        'content_field' => 'message',
                        'handle' => 'contact_us',
                    ],
                    'contact_you' => [
                        'name_field' => 'name3',
                        'first_name_field' => 'first_name',
                        'last_name_field' => 'last_name',
                        'email_field' => 'email3',
                        'content_field' => 'message',
                        'handle' => 'contact_you',
                    ],
                ],
            ]
        );
    }

    public function testThatAdditionalEntriesAreProperlyAdded()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/issues/001/addnew.php',
            __DIR__.'/expected/issues/001/addnew.php',
            [
                'forms.another_form' => [
                    'name_field' => 'name4',
                    'first_name_field' => 'first_name',
                    'last_name_field' => 'last_name',
                    'email_field' => 'email3',
                    'content_field' => 'message',
                    'handle' => 'contact_you',
                ],
            ]
        );
    }

    public function testThatSimpleArraysInsideArraysArePreserved()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/issues/005.php',
            __DIR__.'/expected/issues/005.php',
            [
                'forms' => [[
                    'name_field' => 'name3',
                    'first_name_field' => 'first_name',
                    'last_name_field' => 'last_name',
                    'email_field' => 'email3',
                    'content_field' => 'message',
                    'handle' => 'contact_you',
                    'consent_field' => null,
                ]],
            ]
        );
    }

    public function testThatSimpleArraysInsideArraysArePreservedWhenSourceKeyAlreadyExists()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/issues/005-2.php',
            __DIR__.'/expected/issues/005-2.php',
            [
                'forms' => [[
                    'name_field' => 'name3',
                    'first_name_field' => 'first_name',
                    'last_name_field' => 'last_name',
                    'email_field' => 'email3',
                    'content_field' => 'message',
                    'handle' => 'contact_you',
                    'consent_field' => null,
                ]],
            ]
        );
    }
}
