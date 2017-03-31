<?php

namespace Transitive\Utils;

abstract class Arrays
{
    /*
     * http://stackoverflow.com/questions/8321620/array-unique-vs-array-flip
     */
    public static function array_unique(array $array): array
    {
        return array_flip(array_flip($array));
    }

    public static function is_associative_array(array $array): bool
    {
        return sizeof($array) > 0 && array_keys($array) !== range(0, count($array) - 1);
    }

    // does not keep keys…
    public static function array_flatten(array $array): array
    {
        $flattened = array();

        array_walk_recursive($array, function ($value, $key) use (&$flattened) {
            $flattened[] = $value;
        });

        return $flattened;
    }
}
