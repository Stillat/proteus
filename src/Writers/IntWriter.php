<?php

namespace Stillat\WolfPack\Writers;

use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Scalar\String_;
use Stillat\WolfPack\Contracts\ValueWriterContract;

/**
 * Class IntWriter
 *
 * Provides utilities for converting runtime integer values into mutable node values.
 *
 * @package Stillat\WolfPack\Writers
 * @since 1.0.0
 */
class IntWriter implements ValueWriterContract
{
    const WOLF_INT_SUFFIX_QUOTE = '/*W:INT:SQ*/';
    const WOLF_INT_PREFIX = '/*W:INT:ST*/';

    public function write($value)
    {
        return new Int_(new String_($this->wrap($value)));
    }

    private function wrap($value)
    {
        return self::WOLF_INT_PREFIX . strval($value) . self::WOLF_INT_SUFFIX_QUOTE;
    }
}