<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\Contracts\ValueWriterContract;

/**
 * Class BoolWriter.
 *
 * Provides utilities for converting runtime boolean values to a mutable node value.
 *
 * @since 1.0.0
 */
class BoolWriter implements ValueWriterContract
{
    public function write($value)
    {
        return new Bool_(new String_($value));
    }
}
