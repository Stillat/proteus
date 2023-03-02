<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Stillat\Proteus\Contracts\ValueWriterContract;

/**
 * Class ClassFetchWriter.
 *
 * Provides utilities for converting runtime string values PHP class fetch constants.
 *
 * @since 1.0.6
 */
class ClassFetchWriter implements ValueWriterContract
{
    /**
     * @param  string  $value The input value.
     * @return ClassConstFetch
     */
    public function write($value)
    {
        $fqn = new Name\FullyQualified($value);

        return new ClassConstFetch($fqn, new Identifier('class'));
    }
}
