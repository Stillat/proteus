<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;

/**
 * Class ArrayWriter.
 *
 * Provides utilities for converting runtime arrays to a mutable node value.
 *
 * @since 1.0.0
 */
class ArrayWriter
{
    public function analyze($value)
    {
        $items = [];
        foreach ($value as $keyName => $val) {
            $valueToWrite = TypeWriter::write($val);
            $stringWriter = new StringWriter();

            if (is_string($keyName)) {
                $key = $stringWriter->write($this->wrap($keyName));
                $items[] = new ArrayItem($valueToWrite, $key);
            } else {
                $items[] = new ArrayItem($valueToWrite);
            }
        }

        return new  Array_($items);
    }

    private function wrap($value)
    {
        return $value;
    }
}
