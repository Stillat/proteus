<?php

use PHPUnit\Framework\TestCase;
use Stillat\WolfPack\ConfigUpdater;

if (!function_exists('env')) {
    function env($key, $default= null) {}
}

class ComplexEnvTest extends TestCase
{

    public function testThatEnvReturnCastsArePreserved()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/envcast.php');
        $updater->update([
            'debug' => true,
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);

    }

    public function testThatMultipleEnvCastsSucceed()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/casts.php');
        $expected = file_get_contents(__DIR__ . '/expected/casts.php');
        $updater->update([
            'debug' => false,
            'nested.test' => true,
            'test' => 40,
            'test2' => 60.3,
            'test3' => 'this is another string'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testThatEnvAddsSecondArgumentToSingleArgEnvCalls()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/envaddsdefault.php');
        $updater->update([
            'key' => 'newentry'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

}