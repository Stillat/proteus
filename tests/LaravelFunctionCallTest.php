<?php

namespace Stillat\Proteus\Tests;

use Stillat\Proteus\Writers\FunctionWriter;

class LaravelFunctionCallTest extends ProteusTestCase
{
    public function testThatLaravelResourcePathsAreRewrittenCorrectly()
    {
        $this->assertChangeEquals(
            __DIR__.'/configs/laravelhelpers/resource.php',
            __DIR__.'/expected/laravelhelpers/resource.php',
            [
                'path' => 'new-value',
                'path_two' => 'inserted-value',
                'path_three' => '',
            ]
        );
    }

    public function testEnvCallsCanBeWritten()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/newenv.php',
            [
                'thing' => $f->env('APP_NAME', 'Laravel'),
                'thing2' => $f->env('Something'),
            ]
        );
    }

    public function testExistingEnvCallsCanBeReplaced()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/existingcall.php',
            __DIR__.'/expected/updatedenvcall.php',
            [
                'existing' => $f->env('NEW', 'new-default'),
            ]
        );
    }
}
