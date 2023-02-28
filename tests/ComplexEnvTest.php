<?php

namespace Stillat\Proteus\Tests;

class ComplexEnvTest extends ProteusTestCase
{
    public function testThatEnvReturnCastsArePreserved()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/envcast.php',
            [
                'debug' => true,
            ]
        );
    }

    public function testThatMultipleEnvCastsSucceed()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/casts.php',
            __DIR__.'/expected/casts.php',
            [
                'debug' => false,
                'string' => 'replace',
                'int' => '20',
                'double' => '40.2',
                'bool' => '0',
                'nested.test' => true,
                'test' => 40,
                'test2' => 60.3,
                'test3' => 'this is another string',
            ]
        );
    }

    public function testThatEnvAddsSecondArgumentToSingleArgEnvCalls()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/app.php',
            __DIR__.'/expected/envaddsdefault.php',
            [
                'key' => 'newentry',
            ]
        );
    }
}
