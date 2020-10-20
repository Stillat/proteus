<?php

namespace Stillat\WolfPack\Repository;

use SplFileInfo;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Stillat\WolfPack\ConfigUpdater;
use Symfony\Component\Finder\Finder;

class ConfigRepository extends Repository
{

    /**
     * The loaded configuration files.
     *
     * @var array
     */
    protected $files = [];

    public function __construct(array $items, $app)
    {
        parent::__construct($items);

        $this->files = $this->getConfigurationFiles($app);
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param Application $app
     * @return array
     */
    protected function getConfigurationFiles(Application $app)
    {
        $files = [];

        $configPath = realpath($app->configPath());

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param  SplFileInfo  $file
     * @param  string  $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        return $nested;
    }

    public function write($key, $value)
    {
        $parts = $this->parseKey($key);

        $configUpdater = new ConfigUpdater();
        $configUpdater->open($parts[0]);
        $configUpdater->update([
            $parts[1] => $value
        ]);

        $this->updateContents($parts[0], $configUpdater->getDocument());
    }

    private function updateContents($file, $contents)
    {
        file_put_contents($file, $contents);
    }

    private function parseKey($key)
    {
        $parts = explode('.', $key);
        $config = array_shift($parts);
        $newKey = implode('.', $parts);

        return [
            $this->files[$config],
            $newKey
        ];
    }

}
