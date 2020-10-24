<?php

namespace Stillat\Proteus\Writers;

use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;

class FunctionWriter
{
    const FUNC_LARAVEL_RESOURCE_PATH = 'resource_path';
    const FUNC_LARAVEL_APP_PATH = 'app_path';
    const FUNC_LARAVEL_BASE_PATH = 'base_path';
    const FUNC_LARAVEL_CONFIG_PATH = 'config_path';
    const FUNC_LARAVEL_DATABASE_PATH = 'database_path';
    const FUNC_LARAVEL_MIX_PATH = 'mix';
    const FUNC_LARAVEL_PUBLIC_PATH = 'public_path';
    const FUNC_LARAVEL_STORAGE_PATH = 'storage_path';


    protected function makeSimpleFunctionCall($name, $arg = null)
    {
        $n = new Name($name);
        $args = [];

        if ($arg !== null && mb_strlen($arg) > 0) {
            $stringExp = new String_($arg);
            $arg = new Arg($stringExp);

            $args[] = $arg;
        }

        return new FuncCall($n, $args);
    }

    public function storagePath($path)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_STORAGE_PATH, $path);
    }

    public function publicPath($path)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_PUBLIC_PATH, $path);
    }

    public function appPath($path)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_APP_PATH, $path);
    }

    public function basePath($path)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_BASE_PATH, $path);
    }

    public function configPath($path)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_CONFIG_PATH, $path);
    }

    public function databasePath($path)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_DATABASE_PATH, $path);
    }

    public function resourcePath($path)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_RESOURCE_PATH, $path);
    }

}
