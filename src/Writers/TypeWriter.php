<?php

namespace Stillat\Proteus\Writers;

use Exception;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\FuncCall;
use Stillat\Proteus\Analyzers\TypeAnalyzer;
use Stillat\Proteus\Analyzers\Types;

/**
 * Class TypeWriter
 *
 * Provides a wrapper around the various type writers to convert runtime values to mutable node values.
 *
 * @package Stillat\Proteus\Writers
 * @since 1.0.0
 */
class TypeWriter
{

    /**
     * Attempts to convert the input value into a mutable node value.
     *
     * @param mixed $value The value to convert.
     * @return mixed|Array_|Bool_|Double|Int_|String_|null
     * @throws Exception
     */
    public static function write($value)
    {
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
            $stringWriter = new StringWriter();

            return $stringWriter->write($value);
        } elseif ($type === Types::TYPE_INT) {
            $intWriter= new IntWriter();
            return $intWriter->write($value);
        } elseif ($type === Types::TYPE_BOOL) {
            $boolWriter = new BoolWriter();
            return $boolWriter->write($value);
        }

        return null;
    }

}
