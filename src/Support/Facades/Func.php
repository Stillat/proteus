<?php

namespace Stillat\Proteus\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Stillat\Proteus\Writers\FunctionWriter;

class Func extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FunctionWriter::class;
    }
}
