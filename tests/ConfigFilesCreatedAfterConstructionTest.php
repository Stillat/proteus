<?php

namespace Stillat\Proteus\Tests;

use Stillat\Proteus\LaravelConfigWriter;

class ConfigFilesCreatedAfterConstructionTest extends ProteusTestCase
{
    private $configPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configPath = sys_get_temp_dir().'/proteus-config-'.uniqid();
        mkdir($this->configPath);

        file_put_contents($this->configPath.'/cache.php', <<<'PHP'
<?php

return [
    'stores' => [
    ],
];
PHP);

        $this->app->useConfigPath($this->configPath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir($this->configPath)) {
            foreach (glob($this->configPath.'/*') as $file) {
                unlink($file);
            }

            rmdir($this->configPath);
        }
    }

    public function testItWritesToAConfigFileCreatedAfterTheWriterIsConstructed()
    {
        $writer = new LaravelConfigWriter($this->app, $this->app['config']);

        $this->assertFalse($writer->hasConfig('mail'));
        $this->assertNull($writer->getFile('mail'));

        file_put_contents($this->configPath.'/mail.php', <<<'PHP'
<?php

return [
    'mailers' => [
    ],
];
PHP);

        $saved = $writer->edit('mail')->set('mailers.prime', [
            'transport' => 'prime',
        ])->save();

        $this->assertTrue($saved);
        $this->assertTrue($writer->hasConfig('mail'));

        $mail = require $this->configPath.'/mail.php';

        $this->assertSame(['transport' => 'prime'], $mail['mailers']['prime']);
    }
}
