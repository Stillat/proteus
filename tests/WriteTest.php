<?php

namespace Stillat\Proteus\Tests;

use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

class WriteTest extends ProteusTestCase
{
    public function testWritesCanBeDoneEasilyWithoutTrashingConfigurationFiles()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/014.php');

        $updater->update([
            'stripe' => [
                'reports' => [
                    [
                        'id' => 'a new thing',
                        'frequency' => 'daily',
                        'email_addresses' => 'helloworld@example.org',
                    ],
                    [
                        'id' => 'a new thing!!!',
                        'frequency' => 'dailyasdfasdf',
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
        $updater->open(__DIR__.'/configs/issues/018.php');

        $updater->update([
            'forms' => [
                [
                    'id' => 'new1',
                    'form' => 'contact',
                    'check_consent' => true,
                    'consent_field' => 'newsletter',
                    'disable_opt_in' => false,
                    'marketing_permissions_field_ids' => [
                        [
                        ],
                    ],
                    'merge_fields' => [
                        [
                            'id' => 'new2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'new3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'new4',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'new5',
                ],
            ],
        ], false);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/018d.php'));
        $this->assertSame($expected, $updater->getDocument());
    }

    public function testWritesCanBeDoneEasilyWithoutTrashingConfigurationFilesThree()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/014a.php');

        $updater->update([
            'stripe' => [
                'reports' => [
                    [
                        'id' => 'a new thing',
                        'frequency' => 'daily',
                        'email_addresses' => 'helloworld@example.org',
                    ],
                    [
                        'id' => 'a new thing!!!',
                        'frequency' => 'dailyasdfasdf',
                        'email_addresses' => 'hellasdfasdfasdfasdfoworld@example.org',
                    ],
                ],
            ],
        ], false);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/014b.php'));
        $this->assertSame($expected, $updater->getDocument());
    }

    public function testUpdatingMultipleNestedKeys()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/merge.php');

        $updater->update([
            'stripe' => [
                'reports' => [
                    ['one', 'two'],
                    ['three', 'four'],
                ],
                'test' => [
                    1, 2, 3, 4,
                ],
            ],
        ], false);

        $expected = <<<'EXP'
<?php

return [
    'trackable_videos' => env('SIMPLE_TRACKABLE_VIDEOS', false),

    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'webhook' => env('STRIPE_WEBHOOK_SECRET'),

        // Reporting
        'reports' => [
            [
                'one',
                'two',
            ],
            [
                'three',
                'four',
            ],
        ],
        'leave' => 'this should be ignored.',
        'test' => [
            1,
            2,
            3,
            4,
        ],
    ],

];

EXP;

        $this->assertSame($expected, $updater->getDocument());
    }
}
