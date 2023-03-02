<?php

namespace Stillat\Proteus\Tests;

use Orchestra\Testbench\TestCase;
use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

class ProteusTestCase extends TestCase
{
    protected function assertChangeEquals($configPath, $expectedPath, $changes)
    {
        $updater = new ConfigUpdater();
        $updater->setIgnoreFunctions(false);
        $updater->open($configPath);
        $expected = Transformer::normalizeLineEndings(file_get_contents($expectedPath));
        $updater->update($changes);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    protected function assertReplaceEquals($configPath, $expectedPath, $k, $v)
    {
        $updater = new ConfigUpdater();
        $updater->open($configPath);
        $updater->setIgnoreFunctions(false);
        $expected = Transformer::normalizeLineEndings(file_get_contents($expectedPath));
        $updater->replace($k, $v);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    protected function assertRemoveEquals($configPath, $expectedPath, $k)
    {
        $updater = new ConfigUpdater();
        $updater->setIgnoreFunctions(false);
        $updater->open($configPath);
        $expected = Transformer::normalizeLineEndings(file_get_contents($expectedPath));
        $updater->remove($k);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }
}
