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

    public function testWritingNewClosuresOrArrowFunctions()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/existingcall.php',
            __DIR__.'/expected/closurecalls.php',
            [
                'new2' => $f->closure(function ($content) {
                    return mb_strtolower($content);
                }),
                'new3' => $f->closure(fn ($content) => mb_strtolower($content)),
            ]
        );
    }

    public function testUpdatingAnExistingClosure()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/existing_closure_call.php',
            __DIR__.'/expected/update_closures.php',
            [
                'new2' => $f->closure(function ($content) {
                    return mb_strtolower($content);
                }),
                'new3' => $f->closure(fn ($content2) => mb_strtolower($content2)),
            ]
        );
    }
}
