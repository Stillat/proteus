<?php

namespace Stillat\Proteus\Tests;

use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;
use Stillat\Proteus\LaravelConfigWriter;

class ReplaceManyTest extends ProteusTestCase
{
    public function testReplaceManyMechanismViaConfigUpdater(): void
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issue29.php');

        foreach (['enabled' => true, 'route' => 'my-cp', 'start_page' => 'collections/pages'] as $k => $v) {
            $updater->replace($k, $v);
        }

        $expected = Transformer::normalizeLineEndings(
            file_get_contents(__DIR__.'/expected/replaceMany_issue29.php')
        );
        $this->assertEquals($expected, $updater->getDocument());
    }

    public function testReplaceManyWritesToDisk(): void
    {
        $configPath = $this->app->configPath();
        $fixture = $configPath.'/issue29_replace.php';
        copy(__DIR__.'/configs/issue29.php', $fixture);

        try {
            $writer = new LaravelConfigWriter($this->app, $this->app['config']);
            $writer->replaceMany('issue29_replace', [
                'enabled' => true,
                'route' => 'my-cp',
                'start_page' => 'collections/pages',
            ]);

            $expected = Transformer::normalizeLineEndings(
                file_get_contents(__DIR__.'/expected/replaceMany_issue29.php')
            );
            $this->assertEquals($expected, Transformer::normalizeLineEndings(file_get_contents($fixture)));
        } finally {
            @unlink($fixture);
        }
    }

    public function testPreviewReplaceManyDoesNotWriteToDisk(): void
    {
        $configPath = $this->app->configPath();
        $fixture = $configPath.'/issue29_preview.php';
        $originalContent = file_get_contents(__DIR__.'/configs/issue29.php');
        file_put_contents($fixture, $originalContent);

        try {
            $writer = new LaravelConfigWriter($this->app, $this->app['config']);
            $document = $writer->previewReplaceMany('issue29_preview', [
                'enabled' => true,
                'route' => 'my-cp',
                'start_page' => 'collections/pages',
            ]);

            $expected = Transformer::normalizeLineEndings(
                file_get_contents(__DIR__.'/expected/replaceMany_issue29.php')
            );
            $this->assertEquals($expected, Transformer::normalizeLineEndings($document));
            $this->assertEquals(
                Transformer::normalizeLineEndings($originalContent),
                Transformer::normalizeLineEndings(file_get_contents($fixture))
            );
        } finally {
            @unlink($fixture);
        }
    }
}
