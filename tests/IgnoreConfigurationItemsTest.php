<?php

namespace Stillat\Proteus\Tests;

use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

class IgnoreConfigurationItemsTest extends ProteusTestCase
{
    public function testThatFunctionCallsWereIgnored()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/withenv.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/withenv.php'));
        $updater->update([
            'some_key' => 'new-value',
            'nested.key.value' => 'inserted-value',
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testConfigurationItemsCanBeIgnored()
    {
        $updater = new ConfigUpdater();
        $updater->setPreserveKeys([
            'some_key',
            'nested.key.value',
        ]);

        $updater->open(__DIR__.'/configs/withenv.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/withenv_preserve.php'));
        $updater->update([
            'some_key' => 'new-value',
            'nested.key.value' => 'inserted-value',
            'nested.key.append' => 'Hello, universe!',
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }
}
