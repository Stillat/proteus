<?php

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

if (!function_exists('env')) {
    function env($key, $default = null)
    {
    }
}

class NewRootEntriesAreAddedTest extends TestCase
{

    public function testThatNewRootArrayEntriesAreAdded()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/app.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/simplenewkey.php'));
        $updater->update([
            'new' => [
                'value-one',
                'value-two'
            ],
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatNewKeysUsingDotNotationAreProperlyNested()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/app.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/simpledotnotationkeyset.php'));
        $updater->update([
            'new.key' => 'value'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatNewDeeplyNestedKeysAreCreated()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/app.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/deeplynestedtest.php'));
        $updater->update([
            'new.deeply.nested.key' => [
                'hello',
                'world'
            ]
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatNewSimpleItemsAreAppendedToArrays()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/appendarray.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/appendarray.php'));

        // Using the assignment method should just append to the existing values.
        $updater->update([
            'test' => 'new-value'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatResettingAnArrayReplacesTheArray()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/appendarray.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/arrayreplacesarray.php'));

        // Using the assignment method with an array value should replace an existing array.
        $updater->update([
            'test' => ['new-value']
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatNewNestedEntriesAreProperlyCreated()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/issues/001/setnew.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/issues/001/setnew.php'));

        // Using the assignment method with an array value should replace an existing array.
        $updater->update([
            'forms' => [
                'contact_us' => [
                    'name_field' => 'name',
                    'first_name_field' => 'first_name',
                    'last_name_field' => 'last_name',
                    'email_field' => 'email',
                    'content_field' => 'message',
                    'handle' => 'contact_us',
                ],
                'contact_you' =>
                    [
                        'name_field' => 'name3',
                        'first_name_field' => 'first_name',
                        'last_name_field' => 'last_name',
                        'email_field' => 'email3',
                        'content_field' => 'message',
                        'handle' => 'contact_you',
                    ],
            ]
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatNewNestedEntriesAreProperlyUpdatedOnExistingPlaceholder()
    {

        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/issues/001/fromplaceholder.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/issues/001/fromplaceholder.php'));

        // Using the assignment method with an array value should replace an existing array.
        $updater->update([
            'forms' => [
                'contact_us' => [
                    'name_field' => 'name',
                    'first_name_field' => 'first_name',
                    'last_name_field' => 'last_name',
                    'email_field' => 'email',
                    'content_field' => 'message',
                    'handle' => 'contact_us',
                ],
                'contact_you' =>
                    [
                        'name_field' => 'name3',
                        'first_name_field' => 'first_name',
                        'last_name_field' => 'last_name',
                        'email_field' => 'email3',
                        'content_field' => 'message',
                        'handle' => 'contact_you',
                    ],
            ]
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatAdditionalEntriesAreProperlyAdded()
    {

        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/issues/001/addnew.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/issues/001/addnew.php'));

        // Using the assignment method with an array value should replace an existing array.
        $updater->update([
            'forms.another_form' =>
                [
                    'name_field' => 'name3',
                    'first_name_field' => 'first_name',
                    'last_name_field' => 'last_name',
                    'email_field' => 'email3',
                    'content_field' => 'message',
                    'handle' => 'contact_you',
                ],
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }


}