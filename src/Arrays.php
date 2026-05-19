<?php

namespace Transitive\Utils;

abstract class Arrays
{
    public static function isAssociative(array $array): bool
    {
        return sizeof($array) > 0 && array_keys($array) !== range(0, count($array) - 1);
    }

    /*
     * Flatten recursive arrays
     * does not keep keys…
     *
     * @param array multi-level array
     * @return array flattened array
     */
    public static function flatten(array $array): array
    {
        $flattened = array();

        array_walk_recursive($array, function ($value) use (&$flattened) {
            $flattened[] = $value;
        });

        return $flattened;
    }

    /*
     * shamelessly ripped off from https://stackoverflow.com/a/29526501
     *
     * @param array
     * @param array
     * @return array difference
     */
    public static function diffRecursive(array $array1, array $array2): array
    {
        $result = [];

        foreach ($array1 as $key => $value) {
            if(array_key_exists($key, $array2)) {
                if(is_array($value) && is_array($array2[$key])) {
                    $recursive = self::diffRecursive($value, $array2[$key]);

                    if(count($recursive))
                        $result[$key] = $recursive;
                } elseif($value !== $array2[$key])
                    $result[$key] = $value;
            } elseif(!in_array($value, $array2, true))
                $result[$key] = $value;
        }

        return $result;
    }
}
