<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\Contracts\ValueWriterContract;

/**
 * Class StringWriter.
 *
 * Provides utilities for converting runtime string values into mutable node values.
 *
 * @since 1.0.0
 */
class StringWriter implements ValueWriterContract
{
    /**
     * @param  string  $value The input value.
     * @return String_
     */
    public function write($value)
    {
        return new String_($value);
    }
}
