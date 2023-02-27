<?php

require_once 'ProteusTestCase.php';

use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

class MergeTest extends ProteusTestCase
{
    public function testMergeDoesNotRemoveExistingValues()
    {
        $updater = new ConfigUpdater();
        $updater->setIgnoreFunctions(true);
        $updater->open(__DIR__.'/configs/merge.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/merge.php'));
        $updater->setPreserveKeys([
            'stripe.leave',
        ])->update([
            'stripe' => [
                'leave' => 'hello, there!',
                'reports' => [
                    [
                        'frequency' => 'daily',
                        'email_addresses' => 'entry1',
                    ],
                    [
                        'frequency' => 'daily',
                        'email_addresses' => 'entry2',
                    ],
                    [
                        'frequency' => 'daily',
                        'email_addresses' => 'entry3',
                    ],
                ],
                'test' => [
                    'what' => [
                        'nested' => 'value',
                        'happens' => [
                            1, 2, 3, 'four', 'five', 5, 'six', 'seven' => [8],
                        ],
                    ],
                ],
            ],
        ], true);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

    public function testMergeDoesNotFlattenValues()
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
                [
                    'id' => 'b-new1',
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
                            'id' => 'b-new2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'b-new3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'b-new4',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'b-new5',
                ],
            ],

        ], true);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/018.php'));
        $this->assertSame($expected, $updater->getDocument());

        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/expected/issues/018.php');

        $updater->update([
            'forms' => [
                [
                    'id' => 'c-new1',
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
                            'id' => 'c-new2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'c-new3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'c-new4',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'c-new5',
                ],
                [
                    'id' => 'd-new1',
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
                            'id' => 'd-new2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'd-new3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'd-new4',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'd-new5',
                ],
            ],

        ], true);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/018a.php'));
        $this->assertSame($expected, $updater->getDocument());

        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/expected/issues/018a.php');

        $updater->update([
            'forms' => [
                [
                    'id' => 'c-new1',
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
                            'id' => 'c-new2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'c-new3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'c-new4',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'c-new5',
                ],
                [
                    'id' => 'd-new1',
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
                            'id' => 'd-new2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'd-new3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'd-new4',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'd-new5',
                ],
            ],

        ], false);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/018b.php'));
        $this->assertSame($expected, $updater->getDocument());
    }

    public function testSpecifiedKeysCanBeReplacedWhileMerging()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/issues/018.php');
        $updater->setReplaceKeys(['forms']);

        $updater->update([
            'forms' => [
                [
                    'id' => 'id1',
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
                            'id' => 'id2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'id3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'id',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'aud1',
                ],
                [
                    'id' => 'b-new1',
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
                            'id' => 'b-new2',
                            'field_name' => 'first_name',
                            'tag' => 'FNAME',
                        ],
                        [
                            'id' => 'b-new3',
                            'field_name' => 'last_name',
                            'tag' => 'LNAME',
                        ],
                        [
                            'id' => 'b-new4',
                            'field_name' => 'company',
                            'tag' => 'COMPANY',
                        ],
                    ],
                    'primary_email_field' => 'email',
                    'audience_id' => 'b-new5',
                ],
            ],
        ], true);

        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/018c.php'));
        $this->assertSame($expected, $updater->getDocument());
    }
}
