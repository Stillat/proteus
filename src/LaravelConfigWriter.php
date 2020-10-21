<?php

namespace Stillat\Proteus;

use Illuminate\Support\Str;
use SplFileInfo;
use Illuminate\Contracts\Foundation\Application;
use Stillat\Proteus\Analyzers\RecursiveKeyAnalyzer;
use Stillat\Proteus\Contracts\ConfigWriterContract;
use Stillat\Proteus\Exceptions\ConfigNotFoundException;
use Stillat\Proteus\Exceptions\ConfigNotWriteableException;
use Stillat\Proteus\Exceptions\GuardedConfigurationMutationException;
use Symfony\Component\Finder\Finder;

/**
 * Class LaravelConfigWriter
 *
 * Interacts with the Laravel configuration services to provide config-writing features.
 *
 * @package Stillat\Proteus
 */
class LaravelConfigWriter implements ConfigWriterContract
{
    const KEY_NAMESPACE = 'namespace';
    const KEY_FILEPATH = 'path';
    const KEY_DOCUMENT = 'document';

    /**
     * A mapping of application configuration files and their keys.
     *
     * @var array
     */
    protected $files = [];

    /**
     * The Application implementation instance.
     *
     * @var Application|null
     */
    protected $app = null;

    /**
     * The sorted configuration namespaces.
     *
     * Used to locate the most specific configuration file for a key possible.
     *
     * @var array
     */
    protected $configNamespaces = [];

    /**
     * A list of all configuration levels that can not be modified.
     *
     * @var array
     */
    protected $guardedConfigEntries = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->files = $this->getConfigurationFiles($this->app);

        // Produces a sorted mapping.
        $this->configNamespaces = array_keys($this->files);

        uasort($this->configNamespaces, function ($a, $b) {
            return mb_strlen($a) > mb_strlen($b) ? -1 : 1;
        });
    }

    /**
     * Prevents changes to the specified configuration level.
     *
     * @param string $entry The configuration item.
     */
    public function guard($entry)
    {
        $this->guardedConfigEntries[] = trim($entry);
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

            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param SplFileInfo $file
     * @param string $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested;
    }

    /**
     * Attempts to locate the most specific configuration match available.
     *
     * @param string $key The configuration key.
     * @return array|null
     */
    public function getFile($key)
    {
        foreach ($this->configNamespaces as $configNamespace) {
            if (Str::startsWith($key, $configNamespace)) {
                return [
                    self::KEY_FILEPATH => $this->files[$configNamespace],
                    self::KEY_NAMESPACE => $configNamespace
                ];
            }
        }

        return null;
    }

    /**
     * Checks if a configuration file with the provided key exists.
     *
     * @param string $key The key to check.
     * @return bool
     */
    public function hasConfig($key)
    {
        return array_key_exists($key, $this->files);
    }

    /**
     * Adjusts the provided configuration key with respect to the configuration namespace.
     *
     * @param string $configNamespace The configuration namespace.
     * @param string $configKey The configuration key to update.
     * @return string
     */
    protected function adjustKey($configNamespace, $configKey)
    {
        return mb_substr($configKey, mb_strlen($configNamespace) + 1);
    }

    /**
     * Attempts to change a single configuration item and write the changes to disk.
     *
     * @param string $key The configuration key.
     * @param mixed $value The value to update.
     * @return bool
     * @throws ConfigNotFoundException
     * @throws ConfigNotWriteableException
     * @throws GuardedConfigurationMutationException
     */
    public function write($key, $value)
    {
        $document = $this->preview($key, $value);
        $details = $this->getFile($key);
        $path = $document[self::KEY_FILEPATH];

        $result = file_put_contents($path, $document);

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to apply multiple changes to a configuration namespace.
     *
     * @param string $configNamespace The configuration namespace.
     * @param array $values The key/value pairs to update.
     * @return bool
     * @throws ConfigNotFoundException
     * @throws ConfigNotWriteableException
     * @throws GuardedConfigurationMutationException
     */
    public function writeMany($configNamespace, array $values)
    {
        $document = $this->previewMany($configNamespace, $values);
        $details = $this->getFile($configNamespace);
        $path = $document[self::KEY_FILEPATH];

        $result = file_put_contents($path, $document);

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Checks all requested changes against any restricted configuration levels.
     *
     * @param string $namespace The configuration namespace.
     * @param array $changes The changes to validate.
     * @throws GuardedConfigurationMutationException
     */
    private function checkChangesWithGuard($namespace, array $changes)
    {
        $allDotKeys = RecursiveKeyAnalyzer::getDotKeysRecursively($changes, $namespace);

        foreach ($this->guardedConfigEntries as $guardedConfig) {
            foreach ($allDotKeys as $keyToCheck) {
                if (Str::is($guardedConfig, $keyToCheck)) {
                    throw new GuardedConfigurationMutationException("Cannot modify configuration value at '{$keyToCheck}'.");
                }
            }
        }
    }

    /**
     * Attempts to apply the specified changes to the configuration file.
     *
     * @param string $file The path to the configuration file.
     * @param string $namespace The configuration namespace.
     * @param array $changes The changes to apply.
     * @return string
     * @throws ConfigNotFoundException
     * @throws ConfigNotWriteableException
     * @throws GuardedConfigurationMutationException
     */
    protected function getChanges($file, $namespace, array $changes)
    {
        $this->checkChangesWithGuard($namespace, $changes);

        if (!file_exists($file)) {
            throw new ConfigNotFoundException("Config file for '{$key}' could not be located.");
        }

        if (!is_writable($file)) {
            throw new ConfigNotWriteableException("Config file for '{$key}' is not writeable.");
        }

        $configUpdater = new ConfigUpdater();
        $configUpdater->open($file);
        $configUpdater->update($changes);

        return $configUpdater->getDocument();
    }

    /**
     * Attempts to apply many changes to a source configuration document and return the modified document.
     *
     * @param string $configNamespace The root configuration namespace.
     * @param array $values The key/value mapping of all changes.
     * @return string
     * @throws ConfigNotFoundException
     * @throws ConfigNotWriteableException
     * @throws GuardedConfigurationMutationException
     */
    public function previewMany($configNamespace, array $values)
    {
        $configDetails = $this->getFile($configNamespace);

        if ($configDetails === null) {
            throw new ConfigNotFoundException("Could not locate configuration for '{$key}'.");
        }

        $file = $configDetails[self::KEY_FILEPATH];

        return $this->getChanges($file, $configNamespace, $values);
    }

    /**
     * Attempts to update the source configuration and returns the modified document.
     *
     * @param string $key The configuration item to update.
     * @param mixed $value The value to set.
     * @return string
     * @throws ConfigNotFoundException
     * @throws ConfigNotWriteableException
     * @throws GuardedConfigurationMutationException
     */
    public function preview($key, $value)
    {
        $configDetails = $this->getFile($key);

        if ($configDetails === null) {
            throw new ConfigNotFoundException("Could not locate configuration for '{$key}'.");
        }

        $adjustedKey = $this->adjustKey($configDetails[self::KEY_NAMESPACE], $key);
        $file = $configDetails[self::KEY_FILEPATH];

        return $this->getChanges($file, $configDetails[self::KEY_NAMESPACE], [
            $adjustedKey => $value
        ]);
    }

}