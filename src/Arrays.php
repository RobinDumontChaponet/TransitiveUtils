<?php

namespace Transitive\Utils;

abstract class Arrays
{
    /*
     * Keep only unique values in array
     * explainations here : http://stackoverflow.com/questions/8321620/array-unique-vs-array-flip
     *
     * @param array
     * @return array
     */
    public static function array_unique(array $array): array
    {
        return array_flip(array_flip($array));
    }

    public static function is_associative_array(array $array): bool
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
    public static function array_flatten(array $array): array
    {
        $flattened = array();

        array_walk_recursive($array, function ($value, $key) use (&$flattened) {
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
    public function array_diff_recursive(array $array1, array $array2): array
    {
    $result = [];

    foreach ($array1 as $key => $value)
    {
        //if the key exists in the second array, recursively call this function if it is an array, otherwise check if the value is in array2
        if (array_key_exists($key, $array2)) {
            if (is_array($value)) {
                $recursive = array_diff_recursive($value, $array2[$key]);

                if (count($recursive))
                    $result[$key] = $recursive;
            } elseif (!in_array($value, $array2))
                $result[$key] = $value;
        } elseif (!in_array($value, $array2)) //if the key is not in the second array, check if the value is in the second array (this is a quirk of how array_diff works)
            $result[$key] = $value;
    }

    return $result;
}
}
