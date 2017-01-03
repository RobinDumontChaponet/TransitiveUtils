<?php

namespace Transitive\Utils;

// @TODO !

// use Exception;
use PDOException;
use Transitive\Utils\Database as DB;

abstract class ModelDAO
{
    const TABLE_NAME = '';
    const DATABASE_CONNECTION_ID = 'data';

    protected static function getTableName(): string
    {
        $cc = get_called_class();

        return DB::getDatabaseById(self::DATABASE_CONNECTION_ID)->getTablePrefix().$cc::TABLE_NAME;
    }

    protected static function getConnectionId(): string
    {
        $cc = get_called_class();

        return $cc::DATABASE_CONNECTION_ID;
    }

    protected static function getInstance(): ?\PDO
    {
        return DB::getInstanceById(self::DATABASE_CONNECTION_ID);
    }

    protected static function prepare(string $statement): \PDOStatement
    {
        return self::getInstance()->prepare($statement);
    }

/*    private static function _catch ($funct) {
        try {
            return $funct();
        } catch (PDOExcection $e) {
            die('argh : '.$e->getMessage());
        }
    }
*/

/*
    public static function delete(Model $object) {
        try {
            $statement = self::getInstance()->prepare('DELETE FROM '.self::getTableName().' WHERE id=?');
            $statement->bindValue(1, $object->getId());
            $statement->execute();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br/>');
        }
    }
*/
}
