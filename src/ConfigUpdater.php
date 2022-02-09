<?php

namespace Stillat\Proteus;

use Exception;
use Stillat\Proteus\Analyzers\ArrayAnalyzer;
use Stillat\Proteus\Analyzers\ConfigAnalyzer;
use Stillat\Proteus\Document\Transformer;
use Stillat\Proteus\Exceptions\ConfigNotFoundException;
use Stillat\Proteus\Writers\TypeWriter;

/**
 * Class ConfigUpdater
 *
 * Provides utilities and features to update Laravel-style configuration files.
 *
 * This class is a wrapper around the ConfigAnalyzer and the ArrayAnalyzer.
 *
 * @package Stillat\Proteus
 * @since 1.0.0
 */
class ConfigUpdater
{

    /**
     * The ConfigAnalyzer instance.
     *
     * @var ConfigAnalyzer
     */
    private $analyzer = null;

    /**
     * The ArrayAnalyzer instance.
     *
     * @var ArrayAnalyzer
     */
    private $arrayAnalyzer = null;

    /**
     * The Transformer instance.
     *
     * @var Transformer
     */
    private $transformer = null;

    /**
     * Specifies whether function calls should be ignored when updating configuration files.
     *
     * @var bool
     */
    private $ignoreFunctions = false;

    /**
     * A list of configuration keys that should be preserved.
     * @var array
     */
    private $preserveKeys = [];

    public function __construct()
    {
        $this->transformer = new Transformer();
        $this->arrayAnalyzer = new ArrayAnalyzer();
        $this->analyzer = new ConfigAnalyzer();
    }

    /**
     * Sets whether function calls are ignored when updating the configuration.
     *
     * @param bool $ignore
     * @return $this
     */
    public function setIgnoreFunctions($ignore)
    {
        $this->ignoreFunctions = $ignore;

        $this->analyzer->setIgnoreFunctions($ignore);

        return $this;
    }

    /**
     * Sets a list of configuration keys that should always be preserved.
     *
     * @param array $keys
     * @return $this
     */
    public function setPreserveKeys($keys)
    {
        $this->preserveKeys = $keys;

        return $this;
    }

    /**
     * Attempts to remove the key and its value from the configuration.
     *
     * @param string $key The key to remove.
     * @return bool
     */
    public function remove($key)
    {
        if ($this->analyzer->hasNode($key)) {
            return $this->analyzer->removeNode($key);
        }

        return false;
    }

    /**
     * Returns access to the current ConfigAnalyzer state.
     * Replaces a node's value with the provided new value.
     *
     * @return ConfigAnalyzer
     */
    public function config()
    {
        return $this->analyzer;
    }

    /**
     * Opens the provided file and parses the configuration values.
     *
     * @param string $filePath The file to open.
     * @throws Exception
     * @throws ConfigNotFoundException
     */
    public function open($filePath)
    {
        if (!file_exists($filePath)) {
            throw new ConfigNotFoundException("Configuration file does not exist: {$filePath}");
        }

        $existingConfigItems = require $filePath;
        $this->arrayAnalyzer->analyze($existingConfigItems);

        $this->analyzer->open($filePath);
        $this->analyzer->setValues($existingConfigItems);
    }

    /**
     * Replaces a node's value with the provided new value.
     *
     * @param string $key The key to update.
     * @param mixed $value The value to replace.
     * @throws Exception
     */
    public function replace($key, $value)
    {
        if ($this->analyzer->hasNode($key)) {
            $this->analyzer->replaceNode($key, TypeWriter::write($value));
        }
    }

    /**
     * Replaces an existing node structure.
     *
     * @param string $key The original key.
     * @param string $newKey The new key.
     * @param mixed $value The value to insert.
     * @param string $docBlock The Laravel "block" comment.
     * @param bool $forceNewLine Whether or not to force a new line.
     * @throws Exception
     */
    public function replaceStructure($key, $newKey, $value, $docBlock, $forceNewLine = true)
    {
        if ($this->analyzer->hasNode($key)) {
            $this->analyzer->replaceNodeWithDocBlock($key, $newKey, TypeWriter::write($value), $docBlock, $forceNewLine);
        }
    }

    /**
     * Attempts to apply the requested changes to the existing configuration values.
     *
     * @param array $changes The changes to apply to the existing configuration.
     * @throws Exception
     */
    public function update(array $changes)
    {
        if (! empty($this->preserveKeys)) {
            foreach ($this->preserveKeys as $keyToPreserve) {
                unset($changes[$keyToPreserve]);
            }
        }

        $changesToMake = $this->arrayAnalyzer->getChanges($changes);

        foreach ($changesToMake->insertions as $insert) {
            $valuesToInsert = TypeWriter::write($changes[$insert]);

            if ($this->analyzer->hasNode($insert)) {
                $completeReplace = false;

                if (is_array($changes[$insert]) && count($changes[$insert]) === 0) {
                    $completeReplace = true;
                }

                $this->analyzer->replaceNodeValue($insert, $valuesToInsert, $completeReplace);
            } else {

                if ($this->arrayAnalyzer->isCompound($insert)) {
                    $insertionPoint = $this->arrayAnalyzer->getInsertionPoint($insert);

                    if ($insertionPoint === null) {
                        $root = $this->arrayAnalyzer->getAbsoluteRoot($insert);
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($insert);
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, $changes[$insert]);
                        $valuesToInsert = TypeWriter::write($structure);

                        $this->analyzer->insertValuesAtNode($root, $valuesToInsert);
                    } else {
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($insert);
                        $newKeyValue = array_pop($components);
                        $newVal = [$newKeyValue => $changes[$insert]];

                        array_pop($components);
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, $newVal);
                        $valuesToInsert = TypeWriter::write($structure);

                        $this->analyzer->replaceNodeValue($insertionPoint, $valuesToInsert);
                    }
                    return;
                } else {
                    $this->analyzer->insertValuesAtNode($insert, $valuesToInsert);
                }
            }
        }

        foreach ($changesToMake->updates as $update) {
            $constructedValue = TypeWriter::write($changes[$update]);

            if ($this->analyzer->hasNode($update)) {
                if ($this->analyzer->isNodeArray($update) && is_array($changes[$update]) === false) {
                    $this->analyzer->appendArrayItem($update, $constructedValue);
                } else {
                    $this->analyzer->replaceNodeValue($update, $constructedValue, true);
                }
            } else {
                // How may existing entries are at the desired level?
                $depthCount = $this->arrayAnalyzer->getDepthMatchCount($update);
                $insertPoint = $this->arrayAnalyzer->getInsertionPoint($update);
                $rootReplacement = $this->arrayAnalyzer->getAbsoluteRoot($update);

                if ($this->arrayAnalyzer->hasRoot($rootReplacement)) {
                    if ($depthCount === 0 && $insertPoint === null) {
                        // Most likely a replacement :)
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($update);
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, $changes[$update]);
                        // Rewrite our construction.
                        $constructedValue = TypeWriter::write($structure);

                        $this->analyzer->replaceNodeValue($rootReplacement, $constructedValue);
                    } else {
                        $this->analyzer->insertRootValue($update, $constructedValue);
                    }
                } else {

                    if ($this->arrayAnalyzer->isCompound($update)) {
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($update);
                        $compoundStruct = $this->arrayAnalyzer->getCompoundStructure($components, $changes[$update]);


                        $constructedValue = TypeWriter::write($compoundStruct);

                        $this->analyzer->insertRootValue($rootReplacement, $constructedValue);
                    } else {
                        $this->analyzer->insertRootValue($rootReplacement, $constructedValue);
                    }
                }
            }
        }
    }

    /**
     * Converts the modified configuration to a consumable PHP document.
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->transformer->transform($this->analyzer->dumpConfig());
    }

}
