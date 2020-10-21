<?php

use PHPUnit\Framework\TestCase;
use Stillat\Proteus\ConfigUpdater;

class ClearValuesTest extends TestCase
{

    public function testThatReassigningAnEmptyArrayToAnArrayClearsValues()
    {
        $updater = new ConfigUpdater();
        $updater->open(__DIR__.'/configs/clear/001.php');
        $expected = file_get_contents(__DIR__ . '/expected/clear/001.php');
        $updater->update([
            'nested.new.key' => []
        ]);

        $doc = $updater->getDocument();

        $this->assertEquals($expected, $doc);
    }

}