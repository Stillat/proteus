<?php

namespace Stillat\Proteus\Tests;

class SimpleReplacementTest extends ProteusTestCase
{
    public function testRootReplacementWorks()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/simple_replace.php',
            [
                'timezone' => 'America/Chicago',
                'fallback_locale' => 'fr',
            ]
        );
    }

    public function testEnvCallsAreRetained()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/retain_env.php',
            [
                'name' => 'Statamic',
            ]
        );
    }

    public function testMultipleChangesPreserveEnvCalls()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/multi_replace_env.php',
            [
                'name' => 'Statamic',
                'locale' => 'fr',
                'env' => 'development',
                'debug' => true,
                'url' => 'http://local.test',
            ]
        );
    }

    public function testOutputRetainsUsingStatements()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/configwithusing.php',
            __DIR__.'/expected/configwithusing.php',
            [
                'test' => 'updated-value',
                'test_two' => 'another-value!',
            ]
        );
    }

    public function testThatCustomFunctionsAreRetained()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/retain/func.php',
            __DIR__.'/expected/retain/func.php',
            [
            ]
        );
    }

    public function testThatValuesCanBeCompletelyReplaced()
    {
        $this->assertReplaceEquals(
            __DIR__.'/configs/mail.php',
            __DIR__.'/expected/replace/001.php',
            'from',
            [
                'my-values' => 'hi',
            ]
        );
    }

    public function testThatRootStructuresCanBeReplacedWithComments()
    {
        $updater = new \Stillat\Proteus\ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/008.php');
        $expected = \Stillat\Proteus\Document\Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/008.php'));

        $docBlock = <<<'BLOCK'
/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
|
| Configure the Cart Driver in use on your site. It's what stores/gets the
| Cart ID from the user's browser on every request.
|
*/
BLOCK;

        $updater->replaceStructure('cart_key', 'cart', [
            'driver' => \Stillat\Proteus\ConfigUpdater::class,
            'key' => 'simple-commerce-cart',
        ], $docBlock, true);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }
}
