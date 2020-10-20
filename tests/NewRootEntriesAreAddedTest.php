<?php

use PHPUnit\Framework\TestCase;
use Stillat\WolfPack\ConfigUpdater;

if (!function_exists('env')) {
    function env($key, $default= null) {}
}

class NewRootEntriesAreAddedTest extends TestCase
{

    public function testThatNewRootArrayEntriesAreAdded()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/simplenewkey.php');
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
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/simpledotnotationkeyset.php');
        $updater->update([
            'new.key' => 'value'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatNewDeeplyNestedKeysAreCreated()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/deeplynestedtest.php');
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
        $updater->open(__DIR__.'/configs/appendarray.php');
        $expected = file_get_contents(__DIR__ . '/expected/appendarray.php');

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
        $updater->open(__DIR__.'/configs/appendarray.php');
        $expected = file_get_contents(__DIR__ . '/expected/arrayreplacesarray.php');

        // Using the assignment method with an array value should replace an existing array.
        $updater->update([
            'test' => ['new-value']
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

}