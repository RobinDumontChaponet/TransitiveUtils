<?php

namespace Transitive\Utils;

use PDO;
use PDOException;
use Datetime;

class UserDAO extends ModelDAO
{
    const TABLE_NAME = 'User';

    public static function create(&$user)
    {
        try {
	        self::beginTransaction();

            $statement = self::prepare('INSERT INTO '.self::getTableName().' (emailAddress, pseudonym, passwordHash, cTime, mTime, aTime, sessionHash) values (:emailAddress, :pseudonym, :passwordHash, :cTime, :mTime, :aTime, :sessionHash)');
            $statement->bindValue(':emailAddress', $user->getEmailAddress());
            $statement->bindValue(':pseudonym', $user->getPseudonym());
            $statement->bindValue(':passwordHash', $user->getPasswordHash());

            $statement->bindValue(':cTime', $user->getCreationTime()->getTimestamp());
            $statement->bindValue(':mTime', ($user->getModificationTime())?$user->getModificationTime()->getTimestamp():null);
            $statement->bindValue(':aTime', ($user->getAccessTime())?$user->getAccessTime()->getTimestamp():null);
            $statement->bindValue(':sessionHash', $user->getSessionHash());

            $statement->execute();
            $user->setId(self::getInstance()->lastInsertId());

            foreach($user->getGroups() as $group)
                self::addInGroup($user, $group);

			self::commit();

            return $user->getId();
        } catch (PDOException $e) {
	        self::rollBack();
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function update(&$user)
    {
        try {
	        self::beginTransaction();

            $statement = self::prepare('UPDATE '.self::getTableName().' SET emailAddress=:emailAddress, pseudonym=:pseudonym, passwordHash=:passwordHash, cTime=:cTime, mTime=:mTime, aTime=:aTime, sessionHash=:sessionHash WHERE id=:id');
            $statement->bindValue(':id', $user->getId());
            $statement->bindValue(':emailAddress', $user->getEmailAddress());
            $statement->bindValue(':pseudonym', $user->getPseudonym());
            $statement->bindValue(':passwordHash', $user->getPasswordHash());

            $statement->bindValue(':cTime', $user->getCreationTime()->getTimestamp());
            $statement->bindValue(':mTime', ($user->getModificationTime())?$user->getModificationTime()->getTimestamp():null);
            $statement->bindValue(':aTime', ($user->getAccessTime())?$user->getAccessTime()->getTimestamp():null);
            $statement->bindValue(':sessionHash', $user->getSessionHash());

            $statement->execute();

            self::_updateGroups($user);

            self::commit();

            return self::getInstance()->lastInsertId();
        } catch (PDOException $e) {
	        self::rollBack();
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function getAll(): array
    {
        $objects = array();

        try {
            $statement = self::prepare('SELECT id, emailAddress, pseudonym, cTime, mTime, aTime FROM '.self::getTableName());

            $statement->execute();

            while ($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $objects[$rs->id] = new User($rs->emailAddress, $rs->pseudonym);
                $objects[$rs->id]->setId($rs->id);

				$objects[$rs->id]->setCreationTime(new DateTime('@'.$rs->cTime));
                if($rs->mTime)
					$objects[$rs->id]->setModificationTime(new DateTime('@'.$rs->mTime));
				if($rs->aTime)
					$objects[$rs->id]->setAccessTime(new DateTime('@'.$rs->aTime));
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }

        return $objects;
    }

    public static function getById(int $id): ?User
    {
        $object = null;

        try {
            $statement = self::prepare('SELECT emailAddress, pseudonym, passwordHash, cTime, mTime, aTime, sessionHash FROM '.self::getTableName().' WHERE id=:id');
            $statement->bindParam(':id', $id);
            $statement->execute();

            if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $object = new User($rs->emailAddress, $rs->pseudonym, $rs->passwordHash);
                $object->setId($id);
                $object->setGroups(GroupDAO::getByUser($object));

                $object->setCreationTime(new DateTime('@'.$rs->cTime));
                if($rs->mTime)
	                $object->setModificationTime(new DateTime('@'.$rs->mTime));
				if($rs->aTime)
	                $object->setAccessTime(new DateTime('@'.$rs->aTime));
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }

        return $object;
    }

    public static function getByLogin(string $login): ?User
    {
        $object = null;

        try {
            $statement = self::prepare('SELECT id, pseudonym, passwordHash, cTime, mTime, aTime, sessionHash FROM '.self::getTableName().' WHERE emailAddress=:login');
            $statement->bindParam(':login', $login);
            $statement->execute();

            if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $object = new User($login, $rs->pseudonym, $rs->passwordHash);
                $object->setId($rs->id);
                $object->setGroups(GroupDAO::getByUser($object));

                $object->setCreationTime(new DateTime('@'.$rs->cTime));
                if($rs->mTime)
	                $object->setModificationTime(new DateTime('@'.$rs->mTime));
	            if($rs->aTime)
	                $object->setAccessTime(new DateTime('@'.$rs->aTime));
            }
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }

        return $object;
    }

    public static function addInGroup(User $user, Group $group) {
        try {
            $statement = self::prepare('INSERT INTO `inGroup` (`userId`, `groupId`) VALUES (:userId, :groupId)');
            $statement->bindValue(':userId', $user->getId());
            $statement->bindValue(':groupId', $group->getId());

            $statement->execute();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    public static function removeFromGroup(User $user, Group $group) {
        try {
            $statement = self::prepare('DELETE FROM `inGroup` WHERE `userId`=:userId AND `groupId`=:groupId');
            $statement->bindValue(':userId', $user->getId());
            $statement->bindValue(':groupId', $group->getId());

            $statement->execute();
        } catch (PDOException $e) {
            die(__METHOD__.' : '.$e->getMessage().'<br />');
        }
    }

    private static function _updateGroups(User $user)
    {
        $news = $user->getGroups();
        $olds = GroupDAO::getByUser($user);

        foreach(array_diff($olds, $news) as $removed)
            self::removeFromGroup($user, $removed);

        foreach(array_diff($news, $olds) as $added)
            self::addInGroup($user, $added);
    }

    public static function search(string $pseudonym, int $limit = null, int $offset = null): array
	{
		$objects = array();

        try {
            $statement = self::prepare('SELECT * FROM '.self::getTableName().' WHERE pseudonym LIKE :pseudonym'. (($limit)?' LIMIT :limit':'').(($offset)?' OFFSET :offset':''));
            $statement->bindValue(':pseudonym', '%'.$pseudonym.'%');
            if($limit)
				$statement->bindParam(':limit', $limit, PDO::PARAM_INT);
			if($offset)
	            $statement->bindParam(':offset', $offset, PDO::PARAM_INT);

            $statement->execute();

            while ($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $objects[$rs->id] = new User($rs->emailAddress, $pseudonym);
                $objects[$rs->id]->setId($rs->id);
            }
        } catch (PDOException $e) {
            throw new DAOException($e->getMessage());
        }

        return $objects;
	}
}
