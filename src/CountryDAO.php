<?php

namespace Transitive\Utils;

use PDO;
use PDOException;

abstract class CountryDAO extends ModelDAO
{
    const TABLE_NAME = 'Country';

    public static function create(&$object)
    {
        try {
            $statement = self::prepare('INSERT INTO '.self::getTableName().' (code, alpha2, alpha2, name) values (:code, :alpha2, :alpha2, :name)');
            $statement->bindValue(':code', $object->getCode());
            $statement->bindValue(':alpha2', $object->getAlpha2());
            $statement->bindValue(':alpha3', $object->getAlpha3());
            $statement->bindValue(':name', $object->getName());

            $statement->execute();
            $object->setId(self::lastInsertId());

            return $object->getId();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function update(&$object)
    {
        try {
            $statement = self::prepare('UPDATE '.self::getTableName().' SET code=:code, alpha2=:alpha2, alpha3=:alph3, name=:name WHERE id=:id');
            $statement->bindValue(':code', $object->getCode());
            $statement->bindValue(':alpha2', $object->getAlpha2());
            $statement->bindValue(':alpha3', $object->getAlpha3());
            $statement->bindValue(':name', $object->getName());
            $statement->bindValue(':id', $object->getId());

            $statement->execute();

            return self::getInstance()->lastInsertId();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function getAll(): array
    {
        $objects = array();

        try {
            $statement = self::prepare('SELECT * FROM '.self::getTableName());
            $statement->execute();

            while ($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $objects[$rs->id] = new Country($rs->name, $rs->code, $rs->alpha2, $rs->alpha2);
                $objects[$rs->id]->setId($rs->id);
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }

        return $objects;
    }

    public static function getById(string $id): ?Country
    {
        $object = null;

        try {
            $statement = self::prepare('SELECT * FROM '.self::getTableName().' WHERE id=?');
            $statement->bindParam(1, $id);
            $statement->execute();

            if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $object = new Country($rs->name, $rs->code, $rs->alpha2, $rs->alpha2);
                $object->setId($rs->id);
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }

        return $object;
    }
}
