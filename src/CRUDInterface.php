<?php

namespace Transitive\Utils;

interface CRUDInterface
{
    public static function create(&$object);

    public static function update(&$object);

    public static function delete($object);

    public static function count(): ?int;
}
