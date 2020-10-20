<?php

namespace Stillat\WolfPack;

use Illuminate\Support\ServiceProvider;
use Stillat\WolfPack\Repository\ConfigRepository;

class WriterServiceProvider extends ServiceProvider
{


    public function register()
    {
        $this->app->extend('config', function ($config, $app) {
            return new ConfigRepository($config->all(), $app);
        });
    }

}
