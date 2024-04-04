<?php

namespace Stillat\Proteus\Writers;

use Closure;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
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

    const FUNC_LARAVEL_ENV = 'env';

    protected function makeSimpleFunctionCall(string $name, string|array $args): FuncCall
    {
        return new FuncCall(
            $this->convertToName($name),
            $this->convertToArgs($args)
        );
    }

    public function env(...$args)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_ENV, $args);
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

    public function closure(Closure $closure)
    {
        return $closure;
    }

    private function convertToArgs($args)
    {
        return collect($args)
            ->reject(fn ($arg) => $arg === null || mb_strlen($arg) === 0)
            ->map(fn (string $arg) => new Arg(new String_($arg)))
            ->all();
    }

    private function convertToName(string $name)
    {
        return new Name($name);
    }
}
