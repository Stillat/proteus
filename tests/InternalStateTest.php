<?php

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;

class InternalStateTest extends TestCase
{

    public function testThatSourceNodeKeysAreCorrectlyRetrieved()
    {

        $updater = new ConfigUpdater();
        $updater->open(__DIR__ . '/configs/mail.php');
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
            'from.name',
            'markdown',
            'markdown.theme',
            'markdown.paths'
        ];

        $this->assertEquals($expectedKeys, $keys);
    }


}