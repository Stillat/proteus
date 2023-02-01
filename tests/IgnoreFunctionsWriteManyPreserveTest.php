<?php

require_once 'ProteusTestCase.php';

use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Document\Transformer;

class IgnoreFunctionsWriteManyPreserveTest extends ProteusTestCase
{
    public function testFunctionsAreRestoredWhenWritingItemsAndPreservingKeys()
    {
        $updater = new ConfigUpdater();
        $updater->setIgnoreFunctions(true);
        $updater->open(__DIR__.'/configs/issues/014.php');
        $expected = Transformer::normalizeLineEndings(file_get_contents(__DIR__.'/expected/issues/014.php'));
        $updater->setPreserveKeys([
            'stripe' => [
                'api_base',
                'dg_price_1',
                'dg_product_1',
                'dg_product_2',
                'public',
                'secret',
                'webhook',
                'query_params',
            ],
        ])->update([
            'stripe' => [
                'reports' => [
                    [
                        'id'              => 'a new thing',
                        'frequency'       => 'daily',
                        'email_addresses' => 'helloworld@example.org',
                    ],
                ],
            ],
        ], false); // The false merge should get overridden internally.

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }
}
