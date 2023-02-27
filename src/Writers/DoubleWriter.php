<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\Contracts\ValueWriterContract;

/**
 * Class DoubleWriter.
 *
 * Provides utilities for converting runtime float values to mutable node values.
 *
 * @since 1.0.0
 */
class DoubleWriter implements ValueWriterContract
{
    const PROTEUS_DOUBLE_SUFFIX_QUOTE = '/*W:D:SQ*/';

    const PROTEUS_DOUBLE_PREFIX = '/*W:D:ST*/';

    private function wrap($value)
    {
        return self::PROTEUS_DOUBLE_PREFIX.strval($value).self::PROTEUS_DOUBLE_SUFFIX_QUOTE;
    }

    public function write($value)
    {
        return new Double(new String_($this->wrap($value)));
    }
}
