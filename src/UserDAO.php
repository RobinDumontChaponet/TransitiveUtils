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

            $statement = self::prepare('INSERT INTO '.self::getTableName().' (emailAddress, pseudonym, passwordHash, cTime, mTime, aTime, sessionHash, verified) values (:emailAddress, :pseudonym, :passwordHash, :cTime, :mTime, :aTime, :sessionHash, :verified)');
            $statement->bindValue(':emailAddress', $user->getEmailAddress());
            $statement->bindValue(':pseudonym', $user->getPseudonym());
            $statement->bindValue(':passwordHash', $user->getPasswordHash());

            $statement->bindValue(':cTime', $user->getCreationTime()->getTimestamp());
            $statement->bindValue(':mTime', ($user->getModificationTime()) ? $user->getModificationTime()->getTimestamp() : null);
            $statement->bindValue(':aTime', ($user->getAccessTime()) ? $user->getAccessTime()->getTimestamp() : null);
            $statement->bindValue(':sessionHash', $user->getSessionHash());
//             $statement->bindValue(':verificationHash', $user->getVerificationHash());
            $statement->bindValue(':verified', (int)$user->isVerified());

            $statement->execute();
            $user->setId(self::lastInsertId());

            foreach($user->getGroups() as $group)
                self::addInGroup($user, $group);

            self::commit();

            return $user->getId();
        } catch (PDOException $e) {
            self::rollBack();
            throw new DAOException($e);
        }
    }

    public static function update(&$user)
    {
        try {
            self::beginTransaction();

            $user->setModificationTime(new DateTime());

            $statement = self::prepare('UPDATE '.self::getTableName().' SET emailAddress=:emailAddress, pseudonym=:pseudonym, passwordHash=:passwordHash, cTime=:cTime, mTime=:mTime, aTime=:aTime, sessionHash=:sessionHash, verified=:verified WHERE id=:id');
            $statement->bindValue(':id', $user->getId());
            $statement->bindValue(':emailAddress', $user->getEmailAddress());
            $statement->bindValue(':pseudonym', $user->getPseudonym());
            $statement->bindValue(':passwordHash', $user->getPasswordHash());

            $statement->bindValue(':cTime', $user->getCreationTime()->getTimestamp());
            $statement->bindValue(':mTime', ($user->getModificationTime()) ? $user->getModificationTime()->getTimestamp() : null);
            $statement->bindValue(':aTime', ($user->getAccessTime()) ? $user->getAccessTime()->getTimestamp() : null);
            $statement->bindValue(':sessionHash', $user->getSessionHash());
//             $statement->bindValue(':verificationHash', $user->getVerificationHash());
            $statement->bindValue(':verified', (int)$user->isVerified());

            $statement->execute();

            self::_updateGroups($user);

            self::commit();

            return $user->getId();
        } catch (PDOException $e) {
            self::rollBack();
            throw new DAOException($e);
        }
    }

    public static function getAll(string $sortBy = null, string $orderBy = null, int $limit = null, int $offset = null): array
    {
        $objects = array();

		if(isset($sortBy) && !in_array($sortBy, ['nodeId', 'name', 'meanScore']))
			$sortBy = null;

        try {
            $statement = self::prepare('SELECT id, emailAddress, pseudonym, cTime, mTime, aTime, verified FROM '.self::getTableName() . (($sortBy)?(' ORDER BY '.$sortBy. (($orderBy=='desc')?' DESC':' ASC')):'') . (($limit)?' LIMIT :limit':'').(($offset)?' OFFSET :offset':''));
			if($limit)
				$statement->bindParam(':limit', $limit, PDO::PARAM_INT);
			if($offset)
	            $statement->bindParam(':offset', $offset, PDO::PARAM_INT);

            $statement->execute();

            while ($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $objects[$rs->id] = new User($rs->emailAddress, $rs->pseudonym);
                $objects[$rs->id]->setId($rs->id);
                $objects[$rs->id]->setVerified($rs->verified);

                $objects[$rs->id]->setCreationTime(new DateTime('@'.$rs->cTime));
                if($rs->mTime)
                    $objects[$rs->id]->setModificationTime(new DateTime('@'.$rs->mTime));
                if($rs->aTime)
                    $objects[$rs->id]->setAccessTime(new DateTime('@'.$rs->aTime));
            }
        } catch (PDOException $e) {
            throw new DAOException($e);
        }

        return $objects;
    }

    public static function getById(int $id): ?User
    {
        $object = null;

        try {
            $statement = self::prepare('SELECT emailAddress, pseudonym, passwordHash, cTime, mTime, aTime, sessionHash, verified FROM '.self::getTableName().' WHERE id=:id');
            $statement->bindParam(':id', $id);
            $statement->execute();

            if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $object = new User($rs->emailAddress, $rs->pseudonym, $rs->passwordHash);
                $object->setId($id);
                $object->setGroups(GroupDAO::getByUser($object));
                $object->setVerified($rs->verified);

                $object->setCreationTime(new DateTime('@'.$rs->cTime));
                if($rs->mTime)
                    $object->setModificationTime(new DateTime('@'.$rs->mTime));
                if($rs->aTime)
                    $object->setAccessTime(new DateTime('@'.$rs->aTime));
            }
        } catch (PDOException $e) {
            throw new DAOException($e);
        }

        return $object;
    }

    public static function getByLogin(string $login): ?User
    {
        $object = null;

        try {
            $statement = self::prepare('SELECT id, pseudonym, passwordHash, cTime, mTime, aTime, sessionHash, verified FROM '.self::getTableName().' WHERE emailAddress=:login');
            $statement->bindParam(':login', $login);
            $statement->execute();

            if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
                $object = new User($login, $rs->pseudonym, $rs->passwordHash);
                $object->setId($rs->id);
                $object->setGroups(GroupDAO::getByUser($object));
                $object->setVerified($rs->verified);

                $object->setCreationTime(new DateTime('@'.$rs->cTime));
                if($rs->mTime)
                    $object->setModificationTime(new DateTime('@'.$rs->mTime));
                if($rs->aTime)
                    $object->setAccessTime(new DateTime('@'.$rs->aTime));
            }
        } catch (PDOException $e) {
            throw new DAOException($e);
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
            throw new DAOException($e);
        }
    }

    public static function removeFromGroup(User $user, Group $group) {
        try {
            $statement = self::prepare('DELETE FROM `inGroup` WHERE `userId`=:userId AND `groupId`=:groupId');
            $statement->bindValue(':userId', $user->getId());
            $statement->bindValue(':groupId', $group->getId());

            $statement->execute();
        } catch (PDOException $e) {
            throw new DAOException($e);
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

    public static function search(array $on, string $combinator = 'OR', int $limit = null, int $offset = null): array
	{
		$objects = array();
		$params = array();

        try {
            $statement = self::prepare('SELECT * FROM '.self::getTableName());
            $statement->setLimit($limit);
            $statement->setOffset($offset);

            $statement->setCombinator($combinator);

			$statement->autoBindClause(':pseudonym', @$on['pseudonym'], 'pseudonym LIKE :pseudonym', '', '%');

			$statement->autoBindClause(':emailAddress', @$on['emailAddress'], 'emailAddress LIKE :emailAddress', '', '%');

            if($limit)
				$params[':limit'] = $limit;
			if($offset)
	            $params[':offset'] = $offset;

			$statement->execute();

            while ($rs = $statement->getStatement()->fetch(PDO::FETCH_OBJ)) {
                $objects[$rs->id] = new User($rs->emailAddress, $rs->pseudonym);
                $objects[$rs->id]->setId($rs->id);
            }
        } catch (PDOException $e) {
            throw new DAOException($e);
        }

        return $objects;
	}

	public static function createConfirmation(User $user): ?string
	{
		try {
			$hash = User::createConfirmation();

            $statement = self::prepare('INSERT INTO `userConfirmation` (userId, hash) values (:userId, :hash)');
            $statement->bindValue(':userId', $user->getId());
            $statement->bindValue(':hash', $hash);

            $statement->execute();

            return $hash;
        } catch (PDOException $e) {
            throw new DAOException($e);
        }
	}
	private static function _confirm(User $user)
	{
		if(!$user->isVerified()) {
			$user->setVerified(true);

			try {
	            $statement = self::prepare('DELETE FROM `userConfirmation` WHERE userId=:userId');
	            $statement->bindValue(':userId', $user->getId());

	            $statement->execute();
	        } catch (PDOException $e) {
	            throw new DAOException($e);
	        }

			return self::update($user) != 0;
		}

		return false;
	}
	public static function confirm(User $user, string $hash): bool
    {
		try {
            $statement = self::prepare('SELECT userId FROM `userConfirmation` WHERE userId=:userId AND hash=:hash AND cTime > DATE_SUB(NOW(), INTERVAL 2 DAY)');
            $statement->bindValue(':userId', $user->getId());
            $statement->bindValue(':hash', $hash);

            $statement->execute();

			if($statement->fetch(PDO::FETCH_OBJ) != null)
				return self::_confirm($user);
        } catch (PDOException $e) {
            throw new DAOException($e);
        }

        return false;
    }




    public static function createRecovery(User $user, bool $force = false): ?string
	{
		if($user->isVerified()) {
			try {
				$hash = User::createConfirmation();

	            $statement = self::prepare((($force)?'REPLACE':'INSERT IGNORE'). ' INTO `userRecovery` (userId, hash) values (:userId, :hash)');
	            $statement->bindValue(':userId', $user->getId());
	            $statement->bindValue(':hash', $hash);

	            $statement->execute();

				if($statement->rowCount())
		            return $hash;
	        } catch (PDOException $e) {
	            throw new DAOException($e);
	        }
		}

        return null;
	}
	public static function recover(User $user, string $hash): bool
    {
		try {
            $statement = self::prepare('DELETE FROM `userRecovery` WHERE userId=:userId AND hash=:hash AND cTime > DATE_SUB(NOW(), INTERVAL 2 DAY)');
            $statement->bindValue(':userId', $user->getId());
            $statement->bindValue(':hash', $hash);

            $statement->execute();

			return $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new DAOException($e);
        }

        return false;
    }

	public static function connect(User $user): bool
    {
		$user->connect();

        return UserDAO::update($user) != 0;
    }
}
