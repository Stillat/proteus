<?php

namespace Stillat\Proteus\Tests;

class RemoveValuesTest extends ProteusTestCase
{
    public function testThatArrayElementsCanBeRemovedFromArray()
    {
        $this->assertRemoveEquals(__DIR__.'/configs/mail.php', __DIR__.'/expected/remove/001.php', 'from.address');
    }

    public function testThatEntireArraysCanBeRemoved()
    {
        $this->assertRemoveEquals(__DIR__.'/configs/mail.php', __DIR__.'/expected/remove/002.php', 'from');
    }
}
