<?php

namespace Transitive\Utils;

abstract class DatedDAO extends ModelDAO
{
    public static function create(&$object)
    {
        $object->setCreationTime(time());
    }

    public static function update(&$object)
    {
        $object->setModificationTime(time());
    }
}
