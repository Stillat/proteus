<?php

namespace Stillat\Proteus\Tests;

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;

class InternalStateTest extends TestCase
{
    public function testThatSourceNodeKeysAreCorrectlyRetrieved()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/mail.php');
        $keys = $updater->config()->getSourceNodeKeys();

        $expectedKeys = [
            'default',
            'mailers',
            'mailers.smtp',
            'mailers.smtp.transport',
            'mailers.smtp.host',
            'mailers.smtp.port',
            'mailers.smtp.encryption',
            'mailers.smtp.username',
            'mailers.smtp.password',
            'mailers.smtp.timeout',
            'mailers.smtp.auth_mode',
            'mailers.ses',
            'mailers.ses.transport',
            'mailers.mailgun',
            'mailers.mailgun.transport',
            'mailers.postmark',
            'mailers.postmark.transport',
            'mailers.sendmail',
            'mailers.sendmail.transport',
            'mailers.sendmail.path',
            'mailers.log',
            'mailers.log.transport',
            'mailers.log.channel',
            'mailers.array',
            'mailers.array.transport',
            'from',
            'from.address',
            'from.name',
            'markdown',
            'markdown.theme',
            'markdown.paths',
        ];

        $this->assertEquals($expectedKeys, $keys);
    }

    public function testThatNamespaceImportsAreIgnored()
    {
        $analyzer = new \Stillat\Proteus\Analyzers\ConfigAnalyzer();
        $analyzer->open(__DIR__.'/configs/issues/009.php');
        $root = $analyzer->getRootNode();

        $this->assertNotNull($root);

        if ($root != null) {
            $this->assertSame(get_class($root), \PhpParser\Node\Expr\Array_::class);
        }
    }
}
