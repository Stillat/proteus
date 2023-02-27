<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\Contracts\ValueWriterContract;

/**
 * Class IntWriter.
 *
 * Provides utilities for converting runtime integer values into mutable node values.
 *
 * @since 1.0.0
 */
class IntWriter implements ValueWriterContract
{
    const PROTEUS_INT_SUFFIX_QUOTE = '/*W:INT:SQ*/';

    const PROTEUS_INT_PREFIX = '/*W:INT:ST*/';

    public function write($value)
    {
        return new Int_(new String_($this->wrap($value)));
    }

    private function wrap($value)
    {
        return self::PROTEUS_INT_PREFIX.strval($value).self::PROTEUS_INT_SUFFIX_QUOTE;
    }
}
