<?php

require_once 'ProteusTestCase.php';

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
}
