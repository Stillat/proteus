<?php

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;

if (!function_exists('env')) {
    function env($key, $default= null) {}
}

class SimpleReplacementTest extends TestCase
{


    public function testRootReplacementWorks()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/simple_replace.php');
        $updater->update([
            'timezone' => 'America/Chicago',
            'fallback_locale' => 'fr'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testEnvCallsAreRetained()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/retain_env.php');
        $updater->update([
            'name' => 'Statamic',
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testMultipleChangesPreserveEnvCalls()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/app.php');
        $expected = file_get_contents(__DIR__ . '/expected/multi_replace_env.php');
        $updater->update([
            'name' => 'Statamic',
            'locale' => 'fr',
            'env' => 'development',
            'debug' => true,
            'url' => 'http://local.test'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testOutputRetainsUsingStatements()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/configwithusing.php');
        $expected = file_get_contents(__DIR__ . '/expected/configwithusing.php');
        $updater->update([
            'test' => 'updated-value'
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

}