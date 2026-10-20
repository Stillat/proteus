<?php

namespace Stillat\Proteus\Tests\Writers;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\ConfigUpdater;
use Stillat\Proteus\Tests\ProteusTestCase;
use Stillat\Proteus\Writers\TypeWriter;

class TypeWriterTest extends ProteusTestCase
{
    public function test_exact_class_name_becomes_class_fetch()
    {
        $this->assertInstanceOf(ClassConstFetch::class, TypeWriter::write(ConfigUpdater::class));
    }

    public function test_leading_backslash_class_name_becomes_class_fetch()
    {
        $this->assertInstanceOf(ClassConstFetch::class, TypeWriter::write('\\'.ConfigUpdater::class));
    }

    public function test_wrong_case_class_name_stays_string()
    {
        $value = strtolower(ConfigUpdater::class);
        $node = TypeWriter::write($value);

        $this->assertInstanceOf(String_::class, $node);
        $this->assertSame($value, $node->value);
    }

    public function test_wrong_case_short_class_name_stays_string()
    {
        $node = TypeWriter::write('exception');

        $this->assertInstanceOf(String_::class, $node);
        $this->assertSame('exception', $node->value);
    }
}
