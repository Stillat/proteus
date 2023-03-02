<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use Stillat\Proteus\Contracts\ValueWriterContract;

/**
 * Class NullWriter.
 *
 * Provides utilities for converting `null` values to a valid node.
 *
 * @since 1.0.2
 */
class NullWriter implements ValueWriterContract
{
    /**
     * Writes the provided value.
     *
     * @param  mixed  $value The value to write.
     * @return mixed
     */
    public function write($value)
    {
        $constName = new Name('null');

        return new ConstFetch($constName);
    }
}
