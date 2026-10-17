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

    public function testCustomCallsCanBeWritten()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/newenv.php',
            [
                'thing' => $f->makeSimpleFunctionCall('env', ['APP_NAME', 'Laravel']),
                'thing2' => $f->makeSimpleFunctionCall('env', 'Something'),
            ]
        );
    }

    public function testTypesAreMaintained()
    {
        $f = new FunctionWriter;

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/typesenv.php',
            [
                'int_type' => $f->env('INTEGER_TYPE', 5),
                'double_type' => $f->env('DOUBLE_TYPE', 5.0),
                'string_type' => $f->env('STRING_TYPE', '5'),
                'bool_true' => $f->env('BOOL_TRUE', true),
                'bool_false' => $f->env('BOOL_FALSE', false),
            ]
        );
    }

    public function testEmptyStringDefaultsArePreserved()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/emptystringenv.php',
            [
                'thing' => $f->env('SOME_PATH', ''),
            ]
        );
    }

    public function testPathHelpersSupportNoArguments()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/bareresourcepath.php',
            [
                'thing' => $f->resourcePath(),
            ]
        );
    }

    public function testBasenameCanWrapAPathCall()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/basename.php',
            [
                'thing' => $f->basename($f->basePath()),
            ]
        );
    }

    public function testFunctionCallsCanBeNestedAsArguments()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/nestedfunctioncall.php',
            [
                'thing' => $f->env('CONTENT_PATH', $f->basePath('content')),
            ]
        );
    }

    public function testCastIntCanBeWritten()
    {
        $f = new FunctionWriter();

        $this->assertChangeEquals(
            __DIR__.'/configs/empty.php',
            __DIR__.'/expected/castint.php',
            [
                'thing' => $f->castInt($f->env('KEY', 20)),
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
