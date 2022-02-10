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
        $updater->open(__DIR__ . '/configs/merge.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__ . '/expected/merge.php'));
        $updater->setPreserveKeys([
            'stripe.leave'
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
                            1, 2, 3, 'four', 'five', 5, 'six', 'seven' => [8]
                        ]
                    ]
                ]
            ]
        ], true);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }
}