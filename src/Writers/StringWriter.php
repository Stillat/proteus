<?php

namespace Stillat\WolfPack\Writers;

use PhpParser\Node\Scalar\String_;
use Stillat\WolfPack\Contracts\ValueWriterContract;

/**
 * Class StringWriter
 *
 * Provides utilities for converting runtime string values into mutable node values.
 *
 * @package Stillat\WolfPack\Writers
 * @since 1.0.0
 */
class StringWriter implements ValueWriterContract
{
    /**
     *
     * @param string $value The input value.
     * @return String_
     */
    public function write($value)
    {
        return new String_($value);
    }
}