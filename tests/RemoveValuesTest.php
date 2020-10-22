<?php

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;

if (!function_exists('env')) {
    function env($key, $default= null) {}
}

class RemoveValuesTest extends TestCase
{

    public function testThatArrayElementsCanBeRemovedFromArray()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/mail.php');
        $expected = file_get_contents(__DIR__ . '/expected/remove/001.php');
        $updater->remove('from.address');

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatEntireArraysCanBeRemoved()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/mail.php');
        $expected = file_get_contents(__DIR__ . '/expected/remove/002.php');
        $updater->remove('from');

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

}