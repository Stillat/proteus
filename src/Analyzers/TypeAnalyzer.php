<?php

namespace Stillat\Proteus\Analyzers;

use Closure;
use Exception;

/**
 * Class TypeAnalyzer.
 *
 * Provides utilities to analyze runtime value's types.
 *
 * @since 1.0.0
 */
class TypeAnalyzer
{
    /**
     * Converts the type mapping to a known string representation.
     *
     * @param  int  $type The type.
     * @return string
     */
    public static function typeToString($type)
    {
        if ($type === Types::TYPE_SPECIAL_NULL) {
            return 'null';
        } elseif ($type === Types::TYPE_ARRAY) {
            return 'array';
        } elseif ($type === Types::TYPE_STRING) {
            return 'string';
        } elseif ($type === Types::TYPE_INT) {
            return 'int';
        } elseif ($type === Types::TYPE_DOUBLE) {
            return 'float';
        }

        return '';
    }

    /**
     * Infers the type from the provided value.
     *
     * @param  mixed  $value The value to analyze.
     * @return int
     *
     * @throws Exception
     */
    public static function typeOf($value)
    {
        if ($value === null) {
            return Types::TYPE_SPECIAL_NULL;
        }

        if (is_float($value)) {
            return Types::TYPE_DOUBLE;
        } elseif (is_int($value)) {
            return Types::TYPE_INT;
        } elseif (is_string($value)) {
            return Types::TYPE_STRING;
        } elseif (is_array($value)) {
            return Types::TYPE_ARRAY;
        } elseif (is_bool($value)) {
            return Types::TYPE_BOOL;
        } elseif ($value instanceof Closure) {
            return Types::TYPE_CLOSURE;
        }

        throw new Exception('Support type: '.gettype($value));
    }
}
