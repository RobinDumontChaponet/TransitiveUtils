<?php

namespace Transitive\Utils;

use PDO;
use PDOException;

abstract class MediaDAO extends ModelDAO
{
    const TABLE_NAME = 'Media';

    public static function create(&$object)
    {
        try {
            $statement = self::prepare('INSERT INTO '.self::getTableName().' (type, mimeType, extension, name, title, maxSize) values (:type, :mimeType, :extension, :name, :title, :maxSize)');
            $statement->bindValue(':type', $object->getType());
            $statement->bindValue(':mimeType', $object->getMimeType());
            $statement->bindValue(':extension', $object->getExtension());
            $statement->bindValue(':name', $object->getName());
            $statement->bindValue(':title', $object->getTitle());
            $statement->bindValue(':maxSize', $object->getMaxSize());

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
            $statement = self::prepare('UPDATE '.self::getTableName().' SET type=:type, mimeType=:mimeType, extension=:extension, name=:name, title=:title, maxSize=:maxSize WHERE id=:id');
            $statement->bindValue(':type', $object->getType());
            $statement->bindValue(':mimeType', $object->getMimeType());
            $statement->bindValue(':extension', $object->getExtension());
            $statement->bindValue(':name', $object->getName());
            $statement->bindValue(':title', $object->getTitle());
            $statement->bindValue(':maxSize', $object->getMaxSize());
            $statement->bindValue(':id', $object->getId());

            $statement->execute();

            return self::getInstance()->lastInsertId();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function getAll(string $sortBy = null, string $orderBy = null, int $limit = null, int $offset = null): array
    {
        $objects = array();

        if(!isset($sortBy) || !in_array($sortBy, ['id', 'name', 'mTime'])) {
            $sortBy = null;
            $orderBy = null;
        }

        if(isset($orderBy))
            $orderBy = ('desc' == $orderBy) ? ' DESC' : ' ASC';

        try {
            $statement = self::prepare('SELECT * FROM '.self::getTableName().(($sortBy) ? (' ORDER BY '.$sortBy.$orderBy) : '').(($limit) ? ' LIMIT :limit' : '').(($offset) ? ' OFFSET :offset' : ''));
            if($limit)
                $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
            if($offset)
                $statement->bindParam(':offset', $offset, PDO::PARAM_INT);

            $statement->execute();

            while ($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $objects[$rs->id] = new Media($rs->type, $rs->mimeType, $rs->extension, $rs->maxSize, $rs->name, $rs->title);
                $objects[$rs->id]->setId($rs->id);
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }

        return $objects;
    }

     public static function getById(string $id): ?Media
    {
        $object = null;

        try {
            $statement = self::prepare('SELECT * FROM '.self::getTableName().' WHERE id=?');
            $statement->bindParam(1, $id);
            $statement->execute();

            if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $object = new Media($rs->type, $rs->mimeType, $rs->extension, $rs->maxSize, $rs->name, $rs->title);
                $object->setId($rs->id);
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }

        return $object;
    }

    public static function upload(string $data): ?Media
    {
        $media = new Media();

        // @TODO

        self::create($media);

        return $media;
    }
}
