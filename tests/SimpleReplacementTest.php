<?php

require_once 'ProteusTestCase.php';

class SimpleReplacementTest extends ProteusTestCase
{

    public function testRootReplacementWorks()
    {
        $this->assertChangeEquals(
            __DIR__ . '/configs/app.php',
            __DIR__ . '/expected/simple_replace.php', [
            'timezone' => 'America/Chicago',
            'fallback_locale' => 'fr'
        ]);
    }

    public function testEnvCallsAreRetained()
    {
        $this->assertChangeEquals(
            __DIR__ . '/configs/app.php',
            __DIR__ . '/expected/retain_env.php', [
            'name' => 'Statamic'
        ]);
    }

    public function testMultipleChangesPreserveEnvCalls()
    {
        $this->assertChangeEquals(
            __DIR__ . '/configs/app.php',
            __DIR__ . '/expected/multi_replace_env.php', [
            'name' => 'Statamic',
            'locale' => 'fr',
            'env' => 'development',
            'debug' => true,
            'url' => 'http://local.test'
        ]);
    }

    public function testOutputRetainsUsingStatements()
    {
        $this->assertChangeEquals(
            __DIR__ . '/configs/configwithusing.php',
            __DIR__ . '/expected/configwithusing.php', [
            'test' => 'updated-value'
        ]);
    }

    public function testThatCustomFunctionsAreRetained()
    {
        $this->assertChangeEquals(
            __DIR__ . '/configs/retain/func.php',
            __DIR__ . '/expected/retain/func.php', [
        ]);
    }

    public function testThatValuesCanBeCompletelyReplaced()
    {
        $this->assertReplaceEquals(
            __DIR__ . '/configs/mail.php',
            __DIR__ . '/expected/replace/001.php',
            'from', [
                'my-values' => 'hi'
            ]
        );
    }

}
