<?php

namespace Stillat\Proteus\Analyzers;

/**
 * Class RecursiveKeyAnalyzer.
 *
 * Provides utilities for recursively retrieving key information from arrays.
 */
class RecursiveKeyAnalyzer
{
    /**
     * Returns all nested keys in dot notation for the provided array.
     *
     * @param  array  $values   The key/value pairs to check.
     * @param  string  $startKey The root key.
     * @return array
     */
    public static function getDotKeysRecursively(array $values, $startKey)
    {
        $keys = [];

        foreach ($values as $key => $value) {
            $thisKey = implode('.', [$startKey, $key]);
            $keys[] = $thisKey;

            if (is_array($value)) {
                $keys = array_merge($keys, self::getDotKeysRecursively($value, $thisKey));
            }
        }

        return $keys;
    }
}
