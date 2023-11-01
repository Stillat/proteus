<?php

namespace Stillat\Proteus;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Stillat\Proteus\Analyzers\ArrayAnalyzer;
use Stillat\Proteus\Analyzers\ConfigAnalyzer;
use Stillat\Proteus\Document\Transformer;
use Stillat\Proteus\Exceptions\ConfigNotFoundException;
use Stillat\Proteus\Writers\TypeWriter;

/**
 * Class ConfigUpdater.
 *
 * Provides utilities and features to update Laravel-style configuration files.
 *
 * This class is a wrapper around the ConfigAnalyzer and the ArrayAnalyzer.
 *
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
    private $ignoreFunctions = true;

    /**
     * A list of configuration keys that should be preserved.
     *
     * @var array
     */
    private $preserveKeys = [];

    private $replaceKeys = [];

    private $allowRootRemoval = false;

    private $existingConfig = [];

    public function __construct()
    {
        $this->transformer = new Transformer();
        $this->arrayAnalyzer = new ArrayAnalyzer();
        $this->analyzer = new ConfigAnalyzer();
    }

    /**
     * Sets whether function calls are ignored when updating the configuration.
     *
     * @param  bool  $ignore
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
     * @param  array  $keys
     * @return $this
     */
    public function setPreserveKeys($keys)
    {
        $this->preserveKeys = $this->flattenKeys($keys);

        return $this;
    }

    public function setReplaceKeys($keys)
    {
        $this->replaceKeys = $this->flattenKeys($keys);

        return $this;
    }

    /**
     * Flattens deeply nested arrays using "dot" notation, while preserving root keys.
     *
     * @param  array  $array
     * @param  string  $prefix
     * @return array
     */
    private function flattenKeys($array, $prefix = '')
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $prefixToUse = $prefix;
            if (strlen(trim($prefixToUse)) > 0) {
                $prefixToUse = $prefix.'.';
            }

            if (is_array($value)) {
                $newArray = array_merge($this->flattenKeys($value, $prefixToUse.$key), $newArray);
            } else {
                $newArray[] = $prefixToUse.$value;
            }
        }

        return $newArray;
    }

    /**
     * Attempts to remove the key and its value from the configuration.
     *
     * @param  string  $key The key to remove.
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
     * @param  string  $filePath The file to open.
     *
     * @throws Exception
     * @throws ConfigNotFoundException
     */
    public function open($filePath)
    {
        if (! file_exists($filePath)) {
            throw new ConfigNotFoundException("Configuration file does not exist: {$filePath}");
        }

        $this->existingConfig = require $filePath;
        $this->arrayAnalyzer->analyze($this->existingConfig);

        $this->analyzer->open($filePath);
        $this->analyzer->setValues($this->existingConfig);
    }

    /**
     * Replaces a node's value with the provided new value.
     *
     * @param  string  $key   The key to update.
     * @param  mixed  $value The value to replace.
     *
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
     * @param  string  $key          The original key.
     * @param  string  $newKey       The new key.
     * @param  mixed  $value        The value to insert.
     * @param  string  $docBlock     The Laravel "block" comment.
     * @param  bool  $forceNewLine Whether or not to force a new line.
     *
     * @throws Exception
     */
    public function replaceStructure($key, $newKey, $value, $docBlock, $forceNewLine = true)
    {
        if ($this->analyzer->hasNode($key)) {
            $this->analyzer->replaceNodeWithDocBlock($key, $newKey, TypeWriter::write($value), $docBlock, $forceNewLine);
        }
    }

    private function getStringKeys($array, $prefix = '')
    {
        if (strlen($prefix) > 0) {
            $prefix = $prefix.'.';
        }
        $keys = [];

        foreach ($array as $k => $v) {
            if (is_string($k) && is_array($v)) {
                $keys[] = $prefix.$k;

                $keys = array_merge($keys, $this->getStringKeys($v, $prefix.$k));
            } elseif (is_string($k)) {
                $keys[] = $prefix.$k;
            }
        }

        return $keys;
    }

    private function filterKeys($keys, $preserveValues = false)
    {
        $filtered = [];

        foreach ($keys as $k) {
            if (Str::contains($k, '.')) {
                $filtered[] = $k;
            } else {
                $existingValue = Arr::get($this->existingConfig, $k);

                if ($preserveValues && is_array($existingValue) && ! Arr::isAssoc($existingValue)) {
                    $filtered[] = $k;
                } elseif ($preserveValues && ! is_array($existingValue)) {
                    $filtered[] = $k;
                }
            }
        }

        return $filtered;
    }

    public function allowRootRemoval($allow = true)
    {
        $this->allowRootRemoval = $allow;

        return $this;
    }

    protected function findKeyInArray(Array_ $item, $key)
    {
        foreach ($item->items as $tItem) {
            if (! $tItem->key instanceof String_) {
                continue;
            }

            if ($tItem->key->value == $key) {
                return $tItem;
            }
        }

        return null;
    }

    /**
     * Attempts to apply the requested changes to the existing configuration values.
     *
     * @param  array  $changes The changes to apply to the existing configuration.
     * @param  bool  $isMerge Indicates if merge or forced overwrite behavior should be used.
     *
     * @throws Exception
     */
    public function update(array $changes, $isMerge = false)
    {
        // If we have keys to preserve, writing without all the
        // required values will just stomp all over everything.
        if (count($this->preserveKeys) > 0) {
            $isMerge = true;
        }

        if ($this->ignoreFunctions && count($this->analyzer->getDiscoveredFunctionKeys()) > 0) {
            $this->preserveKeys = array_merge($this->preserveKeys, $this->analyzer->getDiscoveredFunctionKeys());
        }

        $currentConfig = $this->analyzer->getValues();

        $existingKeys = $this->getStringKeys($currentConfig);
        $incomingKeys = $this->getStringKeys($changes);

        if (count($incomingKeys) > 1) {
            $incomingKeys = $this->filterKeys($incomingKeys, true);
            $existingKeys = $this->filterKeys($existingKeys);
        }

        $autoPreserve = array_diff($existingKeys, $incomingKeys);

        $hasCollision = false;

        foreach ($autoPreserve as $k) {
            if (Str::startsWith($k, $incomingKeys)) {
                $hasCollision = true;
            }
        }

        $this->replaceKeys = array_merge($this->replaceKeys, $incomingKeys);

        if (! empty($this->preserveKeys)) {
            foreach ($this->preserveKeys as $keyToPreserve) {
                if ($this->ignoreFunctions && $this->analyzer->containsFunctionCall($keyToPreserve)) {
                    continue;
                }

                unset($changes[$keyToPreserve]);
                Arr::set($changes, $keyToPreserve, Arr::get($currentConfig, $keyToPreserve));
            }
        }

        $potentiallyHiddenFunctionCalls = [];

        if ($this->ignoreFunctions) {
            foreach ($incomingKeys as $incomingKey) {
                if ($this->analyzer->containsFunctionCall($incomingKey)) {
                    $potentiallyHiddenFunctionCalls[] = $incomingKey;
                }
            }
        }

        $changesToMake = $this->arrayAnalyzer->getChanges($changes);
        if ($hasCollision) {
            $changesToMake->updates = $incomingKeys;
        }

        $changesToMake->insertions = array_diff($changesToMake->insertions, $changesToMake->updates);

        if (! $this->allowRootRemoval) {
            $swapRoots = [];
            $swapInsert = [];

            if (count($existingKeys) > 0) {
                foreach ($incomingKeys as $key) {
                    if (! Str::contains($key, '.')) {
                        continue;
                    }

                    $hasExistingParent = false;

                    $parts = explode('.', $key);

                    while (count($parts) >= 1) {
                        $curCheck = implode('.', $parts);
                        if (in_array($curCheck, $existingKeys)) {
                            $hasExistingParent = true;
                            break;
                        }
                        array_pop($parts);
                    }

                    if ($hasExistingParent) {
                        continue;
                    }

                    $root = Str::before($key, '.');

                    if (! in_array($key, $existingKeys)) {
                        $swapRoots[] = $root;
                        $swapInsert[] = $key;
                    }
                }
            }

            if (count($existingKeys) == 0) {
                // And the other way around.
                $shiftRoots = [];
                $removeKeys = [];
                foreach ($incomingKeys as $key) {
                    $root = Str::before($key, '.');

                    if (! in_array($root, $shiftRoots)) {
                        $shiftRoots[] = $root;
                    }
                }

                if (count($shiftRoots) > 0) {
                    $changesToMake->updates = collect($changesToMake->updates)->filter(function ($key) use ($shiftRoots, &$removeKeys) {
                        $isInvalid = ! Str::startsWith($key, $shiftRoots);

                        if (! $isInvalid) {
                            $removeKeys[] = $key;
                        }

                        return $isInvalid;
                    })->values()->all();
                }

                $changesToMake->updates = array_diff($changesToMake->updates, $removeKeys);
                $changesToMake->updates = array_merge($changesToMake->updates, $shiftRoots);
            }

            if (count($swapRoots) > 0) {
                $changesToMake->updates = array_diff($changesToMake->updates, $swapRoots);
                $changesToMake->insertions = array_merge($changesToMake->insertions, $swapInsert);
            }
        }

        $functionRoots = [];

        foreach ($potentiallyHiddenFunctionCalls as $hiddenFunctionCall) {
            if (! Str::contains($hiddenFunctionCall, '.')) {
                continue;
            }

            $functionRoots[] = Str::before($hiddenFunctionCall, '.');
        }

        foreach ($changesToMake->insertions as $insert) {
            $valuesToInsert = TypeWriter::write(Arr::get($changes, $insert, null));

            if ($this->analyzer->hasNode($insert)) {
                /*$completeReplace = false;

                if (is_array($changes[$insert]) && count($changes[$insert]) === 0) {
                    $completeReplace = true;
                }

                $this->analyzer->replaceNodeValue($insert, $valuesToInsert, $completeReplace);*/

                if ($this->ignoreFunctions && $this->analyzer->isNodeArray($insert)) {
                    $originalNode = $this->analyzer->getNode($insert);

                    if ($originalNode instanceof ArrayItem && $originalNode->value instanceof Array_) {
                        // Locate the array.
                        $array = $valuesToInsert;

                        if ($array instanceof ArrayItem && $array->value instanceof Array_) {
                            $array = $array->value;
                        }

                        foreach ($array->items as $tArrayItem) {
                            if (! $tArrayItem->key instanceof String_) {
                                continue;
                            }

                            $checkKey = $insert.'.'.$tArrayItem->key->value;

                            if (! in_array($checkKey, $potentiallyHiddenFunctionCalls)) {
                                continue;
                            }

                            $originalArrayItem = $this->findKeyInArray($originalNode->value, $tArrayItem->key->value);

                            if ($originalArrayItem == null) {
                                continue;
                            }

                            if (! $originalArrayItem->value instanceof FuncCall) {
                                continue;
                            }

                            $tArrayItem->value = $originalArrayItem->value;
                        }
                    }
                }

                $this->analyzer->replaceNodeValue($insert, $valuesToInsert, true);
            } else {
                if ($this->arrayAnalyzer->isCompound($insert)) {
                    $insertionPoint = $this->arrayAnalyzer->getInsertionPoint($insert);

                    if ($insertionPoint === null) {
                        $root = $this->arrayAnalyzer->getAbsoluteRoot($insert);
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($insert);
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, Arr::get($changes, $insert, null));
                        $valuesToInsert = TypeWriter::write($structure);

                        $this->analyzer->insertValuesAtNode($root, $valuesToInsert);
                    } else {
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($insert);
                        $newKeyValue = array_pop($components);
                        $newVal = [$newKeyValue => Arr::get($changes, $insert, null)];

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
            $constructedValue = TypeWriter::write(Arr::get($changes, $update, []));

            if ($this->analyzer->hasNode($update)) {
                if ($this->ignoreFunctions && $this->analyzer->isNodeArray($update)) {
                    $originalNode = $this->analyzer->getNode($update);

                    if ($originalNode instanceof ArrayItem && $originalNode->value instanceof Array_) {
                        // Locate the array.
                        $array = $constructedValue;

                        if ($array instanceof ArrayItem && $array->value instanceof Array_) {
                            $array = $array->value;
                        }

                        foreach ($array->items as $tArrayItem) {
                            if (! $tArrayItem->key instanceof String_) {
                                continue;
                            }

                            $checkKey = $update.'.'.$tArrayItem->key->value;

                            if (! in_array($checkKey, $potentiallyHiddenFunctionCalls)) {
                                continue;
                            }

                            $originalArrayItem = $this->findKeyInArray($originalNode->value, $tArrayItem->key->value);

                            if ($originalArrayItem == null) {
                                continue;
                            }

                            if (! $originalArrayItem->value instanceof FuncCall) {
                                continue;
                            }

                            $tArrayItem->value = $originalArrayItem->value;
                        }
                    }
                }

                if ($this->analyzer->isNodeArray($update) && is_array(Arr::get($changes, $update, null)) === false) {
                    $this->analyzer->appendArrayItem($update, $constructedValue);
                } else {
                    $completeReplace = true;

                    if ($isMerge) {
                        $completeReplace = false;
                    }

                    if (in_array($update, $this->replaceKeys)) {
                        $completeReplace = true;
                    }

                    $this->analyzer->replaceNodeValue($update, $constructedValue, $completeReplace);
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
                        $structure = $this->arrayAnalyzer->getCompoundStructure($components, Arr::get($changes, $update, []));
                        // Rewrite our construction.
                        $constructedValue = TypeWriter::write($structure);

                        $this->analyzer->replaceNodeValue($rootReplacement, $constructedValue);
                    } else {
                        $this->analyzer->insertRootValue($update, $constructedValue);
                    }
                } else {
                    if ($this->arrayAnalyzer->isCompound($update)) {
                        $components = $this->arrayAnalyzer->getCompoundWithoutRoot($update);
                        $compoundStruct = $this->arrayAnalyzer->getCompoundStructure($components, Arr::get($changes, $update, []));

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
