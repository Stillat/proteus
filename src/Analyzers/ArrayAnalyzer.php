<?php

namespace Stillat\Proteus\Analyzers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class ArrayAnalyzer.
 *
 * Analyzes an existing configuration file by loaded it into memory and inspecting its values.
 *
 * @since 1.0.0
 */
class ArrayAnalyzer
{
    /**
     * Provides a mapping between configuration items and their "dot" depth.
     *
     * @var array
     */
    protected $depthMapping = [];

    /**
     * Provides a mapping between configuration keys and their associated Types.
     *
     * @see Types
     *
     * @var array
     */
    protected $keyTypeMapping = [];

    /**
     * Provides a list of all complex/array configuration values.
     *
     * @var array
     */
    protected $complexMapping = [];

    /**
     * Provides a lookup table for all "dot" levels.
     *
     * @var array
     */
    protected $discoveredLevels = [];

    /**
     * Provides a mapping between all dot keys and their values.
     *
     * @var array
     */
    protected $keyValueMapping = [];

    /**
     * Recursively discovers all keys and depth mappings in the value.
     *
     * This is simply a friendly wrapper around analyzeArrayDepth(), and sets up the initial state.
     *
     * @param  array  $value The value to analyze.
     *
     * @throws Exception
     */
    public function analyze(array $value)
    {
        $this->analyzeArrayDepth($value, 0, '');
        $this->discoveredLevels = array_values(array_unique($this->discoveredLevels));
        // This lets us use array_key_exists later instead of iterating each time we need to check a value.
        $this->discoveredLevels = array_combine($this->discoveredLevels, $this->discoveredLevels);
    }

    /**
     * Recursively discovers all keys and depth mappings in the value.
     *
     * @param  array  $value     The values to check.
     * @param  int  $lastDepth The last observed depth.
     * @param  string  $lastKey   The last observed key.
     *
     * @throws Exception
     */
    public function analyzeArrayDepth(array $value, $lastDepth, $lastKey)
    {
        foreach ($value as $key => $v) {
            if ($lastDepth > 0) {
                $dotKey = $lastKey.'.'.$key;
            } else {
                $dotKey = $key;
            }

            if (is_array($v)) {
                $this->analyzeArrayDepth($v, $lastDepth + 1, $dotKey);
            }

            if (! array_key_exists($lastDepth, $this->depthMapping)) {
                $this->depthMapping[$lastDepth] = [];
            }

            if ($lastDepth > 0 && is_array($v)) {
                $this->complexMapping[] = $dotKey;
            }

            $valueType = TypeAnalyzer::typeOf($v);

            // We only want to log level if associated value is an array.
            if ($valueType === Types::TYPE_ARRAY) {
                $this->discoveredLevels[] = $dotKey;
            }

            $this->keyTypeMapping[$dotKey] = $valueType;

            $this->depthMapping[$lastDepth][] = $dotKey;
        }
    }

    /**
     * Checks if a root node with the provided key exists.
     *
     * @param  string  $root The root to check.
     * @return bool
     */
    public function hasRoot($root)
    {
        return array_key_exists($root, $this->keyValueMapping);
    }

    /**
     * Indicates if the provided key is a compound key.
     *
     * test - not compound
     * test.nested - is compound
     *
     * @param  string  $key The key in dot notation.
     * @return bool
     */
    public function isCompound($key)
    {
        return count(explode('.', $key)) > 1;
    }

    /**
     * Checks if the provided key can be augmented (is it an array node?).
     *
     * @param  string  $key The key to check.
     * @return bool
     */
    public function canBeAugmented($key)
    {
        if (array_key_exists($key, $this->keyTypeMapping)) {
            if ($this->keyTypeMapping[$key] === Types::TYPE_ARRAY) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the number of existing nodes that match the provided key.
     *
     * @param  string  $key The key to check.
     * @return int
     */
    public function getDepthMatchCount($key)
    {
        $total = 0;

        foreach ($this->depthMapping as $depthKey => $v) {
            if (Str::startsWith($depthKey, $key)) {
                $total += 1;
            }
        }

        return $total;
    }

    /**
     * Returns the depth mapping.
     *
     * @return array
     */
    public function getDepthMapping()
    {
        return $this->depthMapping;
    }

    /**
     * Returns the key/type mapping.
     *
     * @return array
     */
    public function getKeyTypeMapping()
    {
        return $this->keyTypeMapping;
    }

    /**
     * Returns the complex value mapping.
     *
     * @return array
     */
    public function getComplexMapping()
    {
        return $this->complexMapping;
    }

    /**
     * Returns the discovered levels.
     *
     * @return array
     */
    public function getDiscoveredLevels()
    {
        return $this->discoveredLevels;
    }

    /**
     * Returns the key/value mapping.
     *
     * @return array
     */
    public function getKeyValueMapping()
    {
        return $this->keyValueMapping;
    }

    /**
     * Determines which keys in the input mapping must be updated vs. inserted.
     *
     * @param  array  $updates A mapping of the key/value pairs to update.
     * @return MutationGraph
     */
    public function getChanges($updates)
    {
        $mutationGraph = new MutationGraph();

        foreach ($updates as $key => $value) {
            $point = $this->getInsertionPoint($key);

            if ($point === null) {
                if (is_array($value)) {
                    $subKey = $this->findEndOfStringKeys($value);
                    if (strlen($subKey) > 0) {
                        $mutationGraph->updates[] = $key.'.'.$subKey;
                    } else {
                        $mutationGraph->updates[] = $key;
                    }
                } else {
                    $mutationGraph->updates[] = $key;
                }
            } else {
                $mutationGraph->insertions[] = $key;
            }
        }

        return $mutationGraph;
    }

    private function findEndOfStringKeys($array, $prefix = '')
    {
        if (strlen($prefix) > 0) {
            $prefix = $prefix.'.';
        }

        if (count($array) != 1) {
            return '';
        }

        foreach ($array as $k => $v) {
            if (! is_array($v)) {
                return '';
            }
            if (Arr::isList($v)) {
                return $k;
            } else {
                return $this->findEndOfStringKeys($v, $prefix.$k);
            }
        }

        return '';
    }

    /**
     * Attempts to locate an existing graph node to insert new values on.
     *
     * If this method returns `null`, you will be inserting off the root node.
     *
     * @param  string  $key The key to add, in dot notation.
     * @return string|null
     */
    public function getInsertionPoint($key)
    {
        $parts = explode('.', $key);
        $paths = $this->constructPaths($parts);

        return $this->getFurthestExistingDepth($paths);
    }

    /**
     * Constructs all possible array paths for the given key parts.
     *
     * @param  string[]  $keyParts The key parts.
     * @return array
     */
    public function constructPaths($keyParts)
    {
        $lastKey = '';
        $possiblePaths = [];

        $limit = count($keyParts) - 1;
        for ($i = 0; $i < $limit; $i += 1) {
            if ($i > 0) {
                $lastKey .= '.'.$keyParts[$i];
            } else {
                $lastKey = $keyParts[$i];
            }

            $possiblePaths[] = $lastKey;
        }

        return $possiblePaths;
    }

    /**
     * Checks the existing level graph and finds the furthest existing node from root.
     *
     * @param  string[]  $paths The key paths to check.
     * @return string|null
     */
    public function getFurthestExistingDepth($paths)
    {
        $paths = array_reverse($paths);

        foreach ($paths as $path) {
            if (array_key_exists($path, $this->discoveredLevels)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Returns all of the nested components of the provided key.
     *
     * @param  string  $key The string to analyze.
     * @return string[]
     */
    public function getCompound($key)
    {
        return explode('.', $key);
    }

    /**
     * Returns all of the nested components, without the root value, of the provided key.
     *
     * @param  string  $key The key to analyze.
     * @return string[]
     */
    public function getCompoundWithoutRoot($key)
    {
        $parts = explode('.', $key);

        return array_slice($parts, 1);
    }

    /**
     * Constructs a compound structure with the provided value as the inner-most value.
     *
     * @param  array  $structure The existing structure array.
     * @param  mixed  $value     The value to place inside.
     * @return array|array[]
     */
    public function getCompoundStructure($structure, $value)
    {
        if (count($structure) === 1) {
            $structureNode = array_shift($structure);

            return [
                $structureNode => $value,
            ];
        }

        $structure = array_reverse($structure);

        $lastElement = 0;

        if (! is_array($value)) {
            $structure = array_reverse($structure);
            $lastElement = array_pop($structure);
        }

        $last = [];

        if (is_array($value)) {
            $last = $value;
        } else {
            $last[$lastElement] = $value;
        }

        foreach ($structure as $struct) {
            $last = [
                $struct => $last,
            ];
        }

        return $last;
    }

    /**
     * Returns the 0-th compound element of the provided key.
     *
     * @param  string  $key The key to check.
     * @return string
     */
    public function getAbsoluteRoot($key)
    {
        $parts = explode('.', $key);

        return $parts[0];
    }
}
