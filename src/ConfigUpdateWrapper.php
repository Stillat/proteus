<?php

namespace Stillat\Proteus;

/**
 * Class ConfigUpdateWrapper.
 *
 * Provides a snytax-friendly wrapper around common mutations, allowing
 * for greater control of fine-grained configuration management/updates.
 */
class ConfigUpdateWrapper
{
    const MUTATION_REPLACE = 'replace';

    const MUTATION_REMOVE = 'remove';

    const MUTATION_UPDATE = 'update';

    const MUTATION_REPLACE_STRUCTURE = 'replaceStructure';

    const KEY_MUTATION = 'mutation';

    const KEY_KEY = 'key';

    const KEY_NEW_KEY = 'newKey';

    const KEY_VALUE = 'value';

    const KEY_COMMENT = 'comment';

    const KEY_FORCE_NEWLINE = 'forcenl';

    /**
     * The LaravelConfigWriter instance.
     *
     * @var LaravelConfigWriter|null
     */
    protected $writer = null;

    /**
     * The configuration namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * A collection of mutations to apply to the loaded configuration.
     *
     * @var array
     */
    protected $mutations = [];

    /**
     * A list of known keys for configuration updates.
     *
     * @var array
     */
    protected $knownKeys = [];

    /**
     * ConfigUpdaterWrapper constructor.
     *
     * @param  LaravelConfigWriter  $writer    The writer instance.
     * @param  string  $namespace The configuration namespace.
     */
    public function __construct(LaravelConfigWriter $writer, $namespace)
    {
        $this->writer = $writer;
        $this->namespace = $namespace;
    }

    /**
     * Constructs an absolute namespaced configuration key.
     *
     * @param  string  $relativeKey The relative key.
     * @return string
     */
    private function makeAbsolute($relativeKey)
    {
        return implode('.', [$this->namespace, $relativeKey]);
    }

    /**
     * Merges the provided values with the existing configuration values.
     *
     * @param  string  $key   The configuration usage.
     * @param  array  $value The values to add.
     * @return $this
     */
    public function merge($key, $value)
    {
        $existingValues = $this->writer->getConfigItem($this->makeAbsolute($key));
        $newValue = array_unique(array_merge($existingValues, $value));

        return $this->replace($key, $newValue);
    }

    /**
     * Replaces the existing value at the configuration key's location.
     *
     * @param  string  $key   The configuration key.
     * @param  mixed  $value The new value.
     * @return $this
     */
    public function replace($key, $value)
    {
        $this->knownKeys[] = $this->makeAbsolute($key);

        $this->mutations[] = [
            self::KEY_MUTATION => self::MUTATION_REPLACE,
            self::KEY_VALUE => [
                self::KEY_KEY => $key,
                self::KEY_VALUE => $value,
            ],
        ];

        return $this;
    }

    /**
     * Replaces an existing node structure.
     *
     * @param  string  $key          The original key.
     * @param  string  $newKey       The new key.
     * @param  mixed  $value        The value to insert.
     * @param  string  $docBlock     The Laravel "block" comment.
     * @param  bool  $forceNewLine Whether or not to force a new line.
     * @return $this
     */
    public function replaceStructure($key, $newKey, $value, $docBlock, $forceNewLine = true)
    {
        $this->knownKeys[] = $this->makeAbsolute($key);

        $this->mutations[] = [
            self::KEY_MUTATION => self::MUTATION_REPLACE_STRUCTURE,
            self::KEY_KEY => $key,
            self::KEY_NEW_KEY => $newKey,
            self::KEY_VALUE => $value,
            self::KEY_COMMENT => $docBlock,
            self::KEY_FORCE_NEWLINE => $forceNewLine,
        ];

        return $this;
    }

    /**
     * Removes the value at the configuration key's location, and removes the key.
     *
     * @param  string  $key The configuration key.
     * @return $this
     */
    public function remove($key)
    {
        $this->knownKeys[] = $this->makeAbsolute($key);

        $this->mutations[] = [
            self::KEY_MUTATION => self::MUTATION_REMOVE,
            self::KEY_VALUE => $key,
        ];

        return $this;
    }

    /**
     * Updates the value at the configuration key's location.
     *
     * @param  string  $key   The configuration key.
     * @param  mixed  $value The new value.
     * @return $this
     */
    public function change($key, $value)
    {
        $this->knownKeys[] = $this->makeAbsolute($key);

        $this->mutations[] = [
            self::KEY_MUTATION => self::MUTATION_UPDATE,
            self::KEY_VALUE => [
                $key => $value,
            ],
        ];

        return $this;
    }

    /**
     * Updates a single entry, or multiple if a key/value pair supplied as the key, and no value is set.
     *
     * @param  array|string  $key   The configuration key, or a key/value list of multiple changes.
     * @param  mixed|null  $value The new value.
     * @return $this
     */
    public function set($key, $value = null)
    {
        if ($value === null && is_array($key)) {
            foreach ($key as $setKey => $changeValue) {
                $this->change($setKey, $changeValue);
            }

            return $this;
        } else {
            return $this->change($key, $value);
        }
    }

    /**
     * Attempts to persist the configuration changes to disk.
     *
     * @return bool
     *
     * @throws Exceptions\ConfigNotFoundException
     * @throws Exceptions\ConfigNotWriteableException
     * @throws Exceptions\GuardedConfigurationMutationException
     */
    public function save()
    {
        $details = $this->writer->getFile($this->namespace);

        if ($details === null) {
            return false;
        }

        $path = $details[LaravelConfigWriter::KEY_FILEPATH];
        $document = $this->preview();

        $result = file_put_contents($path, $document);

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Applies all queued changes and returns the modified configuration document.
     *
     * @return string
     *
     * @throws Exceptions\ConfigNotFoundException
     * @throws Exceptions\ConfigNotWriteableException
     * @throws Exceptions\GuardedConfigurationMutationException
     */
    public function preview()
    {
        $this->writer->checkGuard($this->knownKeys);

        // Perform all mutations in the order they were provided.
        $updater = $this->writer->getUpdater($this->namespace);

        foreach ($this->mutations as $mutation) {
            $requestedAction = $mutation[self::KEY_MUTATION];

            if ($requestedAction === self::MUTATION_REPLACE) {
                $mutationValue = $mutation[self::KEY_VALUE];
                $keyToReplace = $mutationValue[self::KEY_KEY];
                $newValue = $mutationValue[self::KEY_VALUE];

                $this->doReplace($updater, $keyToReplace, $newValue);
            } elseif ($requestedAction === self::MUTATION_UPDATE) {
                $this->doUpdate($updater, $mutation[self::KEY_VALUE]);
            } elseif ($requestedAction === self::MUTATION_REMOVE) {
                $this->doRemove($updater, $mutation[self::KEY_VALUE]);
            } elseif ($requestedAction === self::MUTATION_REPLACE_STRUCTURE) {
                $key = $mutation[self::KEY_KEY];
                $newKey = $mutation[self::KEY_NEW_KEY];
                $value = $mutation[self::KEY_VALUE];
                $docBlock = $mutation[self::KEY_COMMENT];
                $forceNl = $mutation[self::KEY_FORCE_NEWLINE];

                $this->doReplaceStructure($updater, $key, $newKey, $value, $docBlock, $forceNl);
            }
        }

        return $updater->getDocument();
    }

    /**
     * @param  ConfigUpdater  $updater      The updater instance.
     * @param  string  $key          The original key.
     * @param  string  $newKey       The new key.
     * @param  mixed  $value        The value to insert.
     * @param  string  $docBlock     The Laravel "block" comment.
     * @param  bool  $forceNewLine Whether or not to force a new line.
     */
    private function doReplaceStructure($updater, $key, $newKey, $value, $docBlock, $forceNewLine = true)
    {
        $updater->replaceStructure($key, $newKey, $value, $docBlock, $forceNewLine);
    }

    /**
     * @param  ConfigUpdater  $updater  The updater instance.
     * @param  string  $key      The key to update.
     * @param  mixed  $newValue The value to insert.
     */
    private function doReplace($updater, $key, $newValue)
    {
        $updater->replace($key, $newValue);
    }

    /**
     * @param  ConfigUpdater  $updater The updater instance.
     * @param  string  $key     The key to remove.
     */
    private function doRemove($updater, $key)
    {
        $updater->remove($key);
    }

    /**
     * @param  ConfigUpdater  $updater The updater instance.
     * @param  array  $update  The key/value pair to update.
     */
    private function doUpdate($updater, $update)
    {
        $updater->update($update);
    }
}
