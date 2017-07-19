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

    public function array_diff_recursive($arr1, $arr2)
    {
    $outputDiff = [];

    foreach ($arr1 as $key => $value)
    {
        //if the key exists in the second array, recursively call this function
        //if it is an array, otherwise check if the value is in arr2
        if (array_key_exists($key, $arr2))
        {
            if (is_array($value))
            {
                $recursiveDiff = array_diff_recursive($value, $arr2[$key]);

                if (count($recursiveDiff))
                {
                    $outputDiff[$key] = $recursiveDiff;
                }
            }
            elseif (!in_array($value, $arr2))
            {
                $outputDiff[$key] = $value;
            }
        }
        //if the key is not in the second array, check if the value is in
        //the second array (this is a quirk of how array_diff works)
        elseif (!in_array($value, $arr2))
        {
            $outputDiff[$key] = $value;
        }
    }

    return $outputDiff;
}
}
