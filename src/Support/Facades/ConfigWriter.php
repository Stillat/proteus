<?php

namespace Stillat\Proteus\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Stillat\Proteus\ConfigUpdateWrapper;
use Stillat\Proteus\Contracts\ConfigWriterContract;
use Stillat\Proteus\Writers\FunctionWriter;

/**
 * @method static bool write($key, $value)
 * @method static bool writeMany($configNamespace, array $values, $isMerge = false)
 * @method static bool mergeMany($configNamespace, array $values)
 * @method static bool hasConfig($key)
 * @method static array|null getFile($key)
 * @method static void guard($entry)
 * @method static string preview($key, $value)
 * @method static string previewMany($configNamespace, array $values, $isMerge = false)
 * @method static ConfigWriterContract ignoreFunctionCalls()
 * @method static ConfigWriterContract preserve($config)
 * @method static ConfigUpdateWrapper edit($namespace)
 * @method static FunctionWriter f()
 *
 * @see \Stillat\Proteus\Contracts\ConfigWriterContract
 */
class ConfigWriter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConfigWriterContract::class;
    }
}
