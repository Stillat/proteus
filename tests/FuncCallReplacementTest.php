<?php

namespace Stillat\Proteus\Tests;

use ReflectionObject;
use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;
use Stillat\Proteus\LaravelConfigWriter;
use Stillat\Proteus\Writers\FunctionWriter;

class FuncCallReplacementTest extends ProteusTestCase
{
    /**
     * Replacing one FuncCall with a different FuncCall should work with the
     * default ignoreFunctions=true, since the caller is making an explicit
     * replacement (not a bulk update where function preservation makes sense).
     */
    public function testReplacingExistingFuncCallWithDifferentFuncCall(): void
    {
        $f = new FunctionWriter();

        $updater = new ConfigUpdater(); // ignoreFunctions=true by default
        $updater->open(__DIR__.'/configs/funcall_replacement.php');
        $updater->update(['path' => $f->basePath('content/revisions')]);

        $expected = Transformer::normalizeLineEndings(
            file_get_contents(__DIR__.'/expected/funcall_replacement.php')
        );

        $this->assertEquals($expected, $updater->getDocument());
    }

    /**
     * LaravelConfigWriter::getUpdater() must propagate the ignoreFunctions
     * setting to the ConfigUpdater it creates, so that
     * ConfigWriter::ignoreFunctionCalls(false) actually takes effect when
     * going through the edit()->set()->save() path.
     */
    public function testGetUpdaterPassesIgnoreFunctionsToConfigUpdater(): void
    {
        // Copy the fixture before constructing LaravelConfigWriter — it scans configPath() on construction.
        $configPath = $this->app->configPath();
        $fixture = $configPath.'/funcall_replacement.php';
        copy(__DIR__.'/configs/funcall_replacement.php', $fixture);

        try {
            $writer = new LaravelConfigWriter($this->app, $this->app['config']);
            $writer->ignoreFunctionCalls(false);

            $updater = $writer->getUpdater('funcall_replacement');

            $updaterRef = new ReflectionObject($updater);
            $updaterProp = $updaterRef->getProperty('ignoreFunctions');

            $this->assertFalse(
                $updaterProp->getValue($updater),
                'getUpdater() should propagate ignoreFunctions=false to the returned ConfigUpdater'
            );
        } finally {
            @unlink($fixture);
        }
    }
}
