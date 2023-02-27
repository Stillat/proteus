<?php

require_once 'ProteusTestCase.php';

use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

class WriteTest extends ProteusTestCase
{

    public function testWritesCanBeDoneEasilyWithoutTrashingConfigurationFiles()
    {
        $updater = new ConfigUpdater();
        $updater->setIgnoreFunctions(true);
        $updater->open(__DIR__.'/configs/issues/014.php');

        $updater->setIgnoreFunctions(true)->update([
            'stripe' => [
                'reports' => [
                    [
                        'id'              => 'a new thing',
                        'frequency'       => 'daily',
                        'email_addresses' => 'helloworld@example.org',
                    ],
                    [
                        'id'              => 'a new thing!!!',
                        'frequency'       => 'dailyasdfasdf',
                        'email_addresses' => 'hellasdfasdfasdfasdfoworld@example.org',
                    ],
                ],
            ],
        ], false);
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/014a.php'));
        $this->assertSame($expected, $updater->getDocument());
    }

    public function testWritesCanBeDoneEasilyWithoutTrashingConfigurationFilesTwo()
    {
        $updater = new ConfigUpdater();
        $updater->setIgnoreFunctions(true);
        $updater->open(__DIR__.'/configs/issues/018.php');

        $updater->setIgnoreFunctions(true)->update([
            'forms' => [
                [
                    'id'                              => 'new1',
                    'form'                            => 'contact',
                    'check_consent'                   => true,
                    'consent_field'                   => 'newsletter',
                    'disable_opt_in'                  => false,
                    'marketing_permissions_field_ids' => [
                        [
                        ],
                    ],
                    'merge_fields' => [
                        [
                            'id'         => 'new2',
                            'field_name' => 'first_name',
                            'tag'        => 'FNAME',
                        ],
                        [
                            'id'         => 'new3',
                            'field_name' => 'last_name',
                            'tag'        => 'LNAME',
                        ],
                        [
                            'id'         => 'new4',
                            'field_name' => 'company',
                            'tag'        => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id'         => 'new5',
                ],
            ]
        ], false); // <-- Note: this is not mergeMany

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/018d.php'));
        $this->assertSame($expected, $updater->getDocument());
    }
}
