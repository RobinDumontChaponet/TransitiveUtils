<?php

namespace Transitive\Utils;

// @TODO !

use Transitive\Utils\Database as DB;

abstract class ModelDAO
{
    const TABLE_NAME = '';
    const DATABASE_CONNECTION_ID = 'data';

    protected static function getTableName() {
        $cc = get_called_class();

        return DB::getDatabaseById(self::DATABASE_CONNECTION_ID)->getTablePrefix().$cc::TABLE_NAME;
    }

    protected static function getConnectionId() {
        $cc = get_called_class();

        return $cc::DATABASE_CONNECTION_ID;
    }

    protected static function getInstance() {
        return DB::getInstanceById(self::DATABASE_CONNECTION_ID);
    }

    protected static function prepare($statement) {
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

    public static function create($object) {
        try {
            $statement = self::prepare('');
// 			$statement->bindValue(1, $user->getLogin());

            $statement->execute();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br/>');
        }
    }

    public static function update($user) {
        try {
            $statement = self::prepare('');
// 			$statement->bindValue(1, $user->getPassword());

            $statement->execute();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br/>');
        }
    }

    public static function delete($object) {
        try {
            $statement = self::getInstance()->prepare('DELETE FROM '.self::getTableName().' WHERE id=?');
            $statement->bindValue(1, $object->getId());
            $statement->execute();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br/>');
        }
    }

    public static function getAll() {
        /*$objects = array();
        try {
            $statement = self::getInstance()->prepare('SELECT * FROM '.self::getTableName().'');

            $statement->execute();

            while ($rs = $statement->fetch(PDO::FETCH_OBJ))
                $objects[] = new User($rs->login, $rs->password);
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br/>');
        }

        return $objects;*/
    }
}
