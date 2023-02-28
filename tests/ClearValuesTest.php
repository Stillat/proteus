<?php

namespace Stillat\Proteus\Tests;

class ClearValuesTest extends ProteusTestCase
{
    public function testThatReassigningAnEmptyArrayToAnArrayClearsValues()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/clear/001.php',
            __DIR__.'/expected/clear/001.php',
            [
                'nested.new.key' => [],
            ]
        );
    }
}
