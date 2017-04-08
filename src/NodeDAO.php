<?php

namespace Transitive\Utils;

use PDO;
use PDOException;

abstract class NodeDAO extends ModelDAO
{
    public static function create(&$object)
    {
        self::beginTransaction();

        $object->setCreationTime(time());

        try {
            $statement = self::prepare('INSERT INTO `Node` (userId, cTime, mTime, aTime) values (:userId, :cTime, :mTime, :aTime)');
            $statement->bindValue(':userId', $object->getUser()->getId());
            $statement->bindValue(':cTime', $object->getCreationTime());
            $statement->bindValue(':mTime', $object->getModificationTime());
            $statement->bindValue(':aTime', $object->getAccessTime());

            $statement->execute();
            $object->setId(self::lastInsertId());

            return $object->getId();
        } catch (PDOException $e) {
            self::rollBack();
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function update(&$object)
    {
        self::beginTransaction();

        $object->setModificationTime(time());

        try {
            $statement = self::prepare('UPDATE `Node` SET userId=:userId, cTime=:cTime, mTime=:mTime, aTime=:aTime WHERE id=:id');
            $statement->bindValue(':userId', $object->getUser()->getId());
            $statement->bindValue(':cTime', $object->getCreationTime());
            $statement->bindValue(':mTime', $object->getModificationTime());
            $statement->bindValue(':aTime', $object->getAccessTime());
            $statement->bindValue(':id', $object->getId());

            $statement->execute();
            $id = self::lastInsertId();

            return $id;
        } catch (PDOException $e) {
            self::rollBack();
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function pushTo(Model &$node): void
    {
	    $id = $node->getId();

        try {
            $statement = self::prepare('SELECT userId, cTime, mTime, aTime FROM `Node` WHERE id=:id');
            $statement->bindParam(':id', $id);
            $statement->execute();

            if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
	            if($rs->userId)
	                $node->setUser(UserDAO::getById($rs->userId));

	            $node->setCreationTime($rs->cTime);
	            $node->setModificationTime($rs->mTime);
	            $node->setAccessTime($rs->aTime);
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }
}
