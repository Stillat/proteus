<?php

namespace Stillat\Proteus\Writers;

use Closure;
use Exception;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\Analyzers\TypeAnalyzer;
use Stillat\Proteus\Analyzers\Types;
use Stillat\Proteus\ClosureParser;

/**
 * Class TypeWriter.
 *
 * Provides a wrapper around the various type writers to convert runtime values to mutable node values.
 *
 * @since 1.0.0
 */
class TypeWriter
{
    /**
     * Attempts to convert the input value into a mutable node value.
     *
     * @param  mixed  $value The value to convert.
     * @return mixed|Array_|Bool_|float|Int_|String_|null
     *
     * @throws Exception
     */
    public static function write($value)
    {
        if ($value instanceof Closure) {
            $serialized = new ReflectionClosure($value);
            $closureParser = new ClosureParser();

            return $closureParser->parse($serialized->getCode());
        }

        if ($value instanceof FuncCall) {
            return $value;
        }

        $type = TypeAnalyzer::typeOf($value);

        if ($type === Types::TYPE_ARRAY) {
            $arrayWriter = new ArrayWriter();

            return $arrayWriter->analyze($value);
        } elseif ($type === Types::TYPE_DOUBLE) {
            $doubleWriter = new DoubleWriter();

            return $doubleWriter->write($value);
        } elseif ($type === Types::TYPE_STRING) {
            if (class_exists($value)) {
                $classWriter = new ClassFetchWriter();

                return $classWriter->write($value);
            }
            $stringWriter = new StringWriter();

            return $stringWriter->write($value);
        } elseif ($type === Types::TYPE_INT) {
            $intWriter = new IntWriter();

            return $intWriter->write($value);
        } elseif ($type === Types::TYPE_BOOL) {
            $boolWriter = new BoolWriter();

            return $boolWriter->write($value);
        } elseif ($type === Types::TYPE_SPECIAL_NULL) {
            $nullWriter = new NullWriter();

            return $nullWriter->write(null);
        }

        return null;
    }
}
