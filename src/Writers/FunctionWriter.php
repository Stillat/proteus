<?php

namespace Stillat\Proteus\Writers;

use Closure;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
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

    public function makeSimpleFunctionCall(string $name, mixed $args): FuncCall
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

    public function storagePath($path = null)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_STORAGE_PATH, $path);
    }

    public function publicPath($path = null)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_PUBLIC_PATH, $path);
    }

    public function appPath($path = null)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_APP_PATH, $path);
    }

    public function basePath($path = null)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_BASE_PATH, $path);
    }

    public function configPath($path = null)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_CONFIG_PATH, $path);
    }

    public function databasePath($path = null)
    {
        return $this->makeSimpleFunctionCall(self::FUNC_LARAVEL_DATABASE_PATH, $path);
    }

    public function resourcePath($path = null)
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
            ->reject(fn ($arg) => $arg === null)
            ->map(fn (mixed $arg) => $this->arg($arg))
            ->all();
    }

    private function convertToName(string $name)
    {
        return new Name($name);
    }

    private function scalarClass(string $type): string
    {
        return match ($type) {
            'integer' => Int_::class,
            'double' => Float_::class,
            default => String_::class,
        };
    }

    private function arg(mixed $arg): Arg
    {
        if ($arg instanceof Expr) {
            return new Arg($arg);
        }

        if (is_bool($arg)) {
            return new Arg(new ConstFetch(new Name($arg ? 'true' : 'false')));
        }

        $scalarClass = $this->scalarClass(gettype($arg));

        return new Arg(new $scalarClass($arg));
    }
}
