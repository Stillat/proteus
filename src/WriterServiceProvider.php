<?php

namespace Stillat\Proteus;

use Illuminate\Support\ServiceProvider;
use Stillat\Proteus\Contracts\ConfigWriterContract;
use Stillat\Proteus\Repository\ConfigRepository;
use Stillat\Proteus\Writers\FunctionWriter;

/**
 * Class WriterServiceProvider
 *
 * Registers the required dependencies to make Proteus work.
 *
 * @package Stillat\Proteus
 */
class WriterServiceProvider extends ServiceProvider
{


    public function register()
    {
        $this->app->singleton(FunctionWriter::class, function () {
            return new FunctionWriter();
        });
        $this->app->singleton(ConfigWriterContract::class, LaravelConfigWriter::class);
    }

}
