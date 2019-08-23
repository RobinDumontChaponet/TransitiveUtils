<?php

namespace Transitive\Utils;

use Transitive\Utils\Database as DB;

abstract class ModelDAO implements CRUDInterface
{
    const TABLE_NAME = '';
    const DATABASE_CONNECTION_ID = 'data';

    public const MYSQLDATE = 'Y-m-d H:i:s';

    protected static function getTableName(): string
    {
        $cc = get_called_class();

        return '`'.DB::getDatabaseById(self::DATABASE_CONNECTION_ID)->getTablePrefix().$cc::TABLE_NAME.'`';
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

    protected static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    protected static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    protected static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }

    protected static function lastInsertId(string $name = null): string
    {
        return self::getInstance()->lastInsertId($name);
    }

    protected static function prepare(string $statement): \PDOStatement
    {
        return self::getInstance()->prepare($statement);
    }

    public static function delete($object)
    {
        $statement = self::prepare('DELETE FROM '.self::getTableName().' WHERE id=?');
        $statement->bindValue(1, $object->getId());
        $statement->execute();

        return $statement->rowCount();
    }

    public static function count(): ?int
    {
        $statement = self::prepare('SELECT COUNT(*) AS c FROM '.self::getTableName());

        $statement->execute();

        if ($rs = $statement->fetch(\PDO::FETCH_OBJ)) {
            return $rs->c;
        }
    }
}
