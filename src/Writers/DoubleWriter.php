<?php

namespace Stillat\WolfPack\Writers;

use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Scalar\String_;
use Stillat\WolfPack\Contracts\ValueWriterContract;

/**
 * Class DoubleWriter
 *
 * Provides utilities for converting runtime float values to mutable node values.
 *
 * @package Stillat\WolfPack\Writers
 * @since 1.0.0
 */
class DoubleWriter implements ValueWriterContract
{
    const WOLF_DOUBLE_SUFFIX_QUOTE = '/*W:D:SQ*/';
    const WOLF_DOUBLE_PREFIX = '/*W:D:ST*/';

    private function wrap($value)
    {
        return self::WOLF_DOUBLE_PREFIX.strval($value).self::WOLF_DOUBLE_SUFFIX_QUOTE;
    }

    public function write($value)
    {
        return new Double(new String_($this->wrap($value)));
    }

}