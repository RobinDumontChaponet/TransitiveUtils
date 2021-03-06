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

			$statement = self::prepare('INSERT INTO '.self::getTableName().' (`emailAddress`, `passwordHash`, `sessionHash`, `verified`, `oauthProvider`, `oauthUid`) values (:emailAddress, :passwordHash, :sessionHash, :verified, :oauthProvider, :oauthUid)');

			$statement->bindValue(':emailAddress', $user->getEmailAddress());

			$statement->bindValue(':passwordHash', $user->getPasswordHash());
			$statement->bindValue(':sessionHash', $user->getSessionHash());

			$statement->bindValue(':verified', (int) $user->isVerified());

			$statement->bindValue(':oauthProvider', $user->getOauthProvider() );
			$statement->bindValue(':oauthUid', $user->getOauthUid());

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

			$statement = self::prepare('UPDATE '.self::getTableName().' SET `emailAddress`=:emailAddress, `passwordHash`=:passwordHash, `sessionHash`=:sessionHash, `verified`=:verified, `oauthProvider`=:oauthProvider, `oauthUid`=:oauthUid, `mTime`=:mTime, `aTime`=:aTime WHERE id=:id');

			$statement->bindValue(':id', $user->getId());

			$statement->bindValue(':emailAddress', $user->getEmailAddress());

			$statement->bindValue(':passwordHash', $user->getPasswordHash());
			$statement->bindValue(':sessionHash', $user->getSessionHash());

			$statement->bindValue(':verified', (int) $user->isVerified());

			$statement->bindValue(':oauthProvider', $user->getOauthProvider() );
			$statement->bindValue(':oauthUid', $user->getOauthUid());

			$statement->bindValue(':mTime', self::mysqlDateTime($user->getModificationTime()));
			$statement->bindValue(':aTime', self::mysqlDateTime($user->getAccessTime()));

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

		if(isset($sortBy) && !in_array($sortBy, ['id', 'emailAddress', 'aTime']))
			$sortBy = null;

		try {
			$statement = self::prepare('SELECT id, emailAddress, verified, _cTime, mTime, aTime FROM '.self::getTableName().(($sortBy) ? (' ORDER BY '.$sortBy.(('desc' == $orderBy) ? ' DESC' : ' ASC')) : '').(($limit) ? ' LIMIT :limit' : '').(($offset) ? ' OFFSET :offset' : ''));
			if($limit)
				$statement->bindParam(':limit', $limit, PDO::PARAM_INT);
			if($offset)
				$statement->bindParam(':offset', $offset, PDO::PARAM_INT);

			$statement->execute();

			while ($rs = $statement->fetch(PDO::FETCH_OBJ)) {
				$objects[$rs->id] = new User($rs->emailAddress);
				$objects[$rs->id]->setId($rs->id);
				$objects[$rs->id]->setVerified($rs->verified);

				$objects[$rs->id]->setCreationTime(new DateTime($rs->_cTime));
				$objects[$rs->id]->setModificationTime(($rs->mTime)? new DateTime($rs->mTime) : null);
				$objects[$rs->id]->setAccessTime(($rs->aTime)? new DateTime($rs->aTime) : null);
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
			$statement = self::prepare('SELECT emailAddress, passwordHash, _cTime, mTime, aTime, sessionHash, verified FROM '.self::getTableName().' WHERE id=:id');
			$statement->bindParam(':id', $id);
			$statement->execute();

			if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
				$object = new User($rs->emailAddress, $rs->passwordHash);
				$object->setId($id);
				$object->setGroups(GroupDAO::getByUser($object));
				$object->setVerified($rs->verified);
				$object->setSessionHash($rs->sessionHash);

				$object->setCreationTime(new DateTime($rs->_cTime));
				$object->setModificationTime(($rs->mTime)? new DateTime($rs->mTime) : null);
				$object->setAccessTime(($rs->aTime)? new DateTime($rs->aTime) : null);
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
			$statement = self::prepare('SELECT id, passwordHash, _cTime, mTime, aTime, sessionHash, verified FROM '.self::getTableName().' WHERE emailAddress=:login');
			$statement->bindParam(':login', $login);
			$statement->execute();

			if($rs = $statement->fetch(PDO::FETCH_OBJ)) {
				$object = new User($login, $rs->passwordHash);
				$object->setId($rs->id);
				$object->setGroups(GroupDAO::getByUser($object));
				$object->setVerified($rs->verified);

				$object->setCreationTime(new DateTime($rs->_cTime));
				$object->setModificationTime(($rs->mTime)? new DateTime($rs->mTime) : null);
				$object->setAccessTime(($rs->aTime)? new DateTime($rs->aTime) : null);
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

/*
			$statement->autoBindClause(':pseudonym', @$on['pseudonym'], 'pseudonym LIKE :pseudonym', '', '%');
			$statement->autoBindClause(':pseudonym', @$on['pseudonym-exact'], 'pseudonym LIKE :pseudonym');
*/

			$statement->autoBindClause(':emailAddress', @$on['emailAddress'], 'emailAddress LIKE :emailAddress', '', '%');

			if($limit)
				$params[':limit'] = $limit;
			if($offset)
				$params[':offset'] = $offset;

			$statement->execute();

			while ($rs = $statement->getStatement()->fetch(PDO::FETCH_OBJ)) {
				$objects[$rs->id] = new User($rs->emailAddress);
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
			$hash = User::randHash();

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

			return 0 != self::update($user);
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

			if(null != $statement->fetch(PDO::FETCH_OBJ))
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
				$hash = User::randHash();

				$statement = self::prepare((($force) ? 'REPLACE' : 'INSERT IGNORE').' INTO `userRecovery` (userId, hash) values (:userId, :hash)');
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

		return 0 != self::update($user);
	}
}
