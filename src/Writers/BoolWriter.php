<?php

namespace Stillat\WolfPack\Writers;

use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Scalar\String_;
use Stillat\WolfPack\Contracts\ValueWriterContract;

/**
 * Class BoolWriter
 *
 * Provides utilities for converting runtime boolean values to a mutable node value.
 *
 * @package Stillat\WolfPack\Writers
 * @since 1.0.0
 */
class BoolWriter implements ValueWriterContract
{

    public function write($value)
    {
        return new Bool_(new String_($value));
    }

}