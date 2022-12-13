<?php

namespace Transitive\Utils;

use PDO;
use Exception;
use PDOException;

class Database
{
    private static $PDOInstances = array();
    private static $databases = array();

    private $dbType;
    private $dbHost;
    private $dbPort;
    private $dbUser;
    private $dbPwd;
    private $dbName;
    private $tablePrefix;

    public function __construct(string $dbName, string  $dbUser, string $dbPwd = '', string $dbType = 'mysql', string $dbHost = 'localhost', string $dbPort = '3306', string $tablePrefix = '')
    {
        $this->dbType = $dbType;
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->dbUser = $dbUser;
        $this->dbPwd = $dbPwd;
        $this->dbName = $dbName;
        $this->tablePrefix = $tablePrefix;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    public function setTablePrefix(string $value): void
    {
        $this->tablePrefix = $value;
    }

    public static function addDatabase(string $id, self $database): void
    {
        self::$databases[$id] = $database;
    }

    public function getInstance(): ?PDO
    {
        if (!isset(self::$PDOInstances[$this->dbType.':'.$this->dbName.','.$this->dbUser])) {
            try {
                $pdo = new PDO(
                    $this->dbType.':host='.$this->dbHost.';port='.$this->dbPort.';dbname='.$this->dbName,
                    $this->dbUser,
                    $this->dbPwd,
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_PERSISTENT => false,
						PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    )
                );
                $pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Transitive\Utils\Statement', array($pdo)));

                // Set prepared statement emulation depending on server version - thx to https://stackoverflow.com/a/10455228
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, version_compare($pdo->getAttribute(PDO::ATTR_SERVER_VERSION), '5.1.17', '<'));

                self::$PDOInstances[$this->dbType.':'.$this->dbName.','.$this->dbUser] = $pdo;
            } catch (PDOException $e) {
                echo '<b>Error '.__METHOD__.' </b> '.$e->getMessage().'<br />'.PHP_EOL;
            }
        }

        return self::$PDOInstances[$this->dbType.':'.$this->dbName.','.$this->dbUser];
    }

    public static function getInstanceById($id): ?PDO {
        if(!isset(self::$databases[$id]))
            throw new Exception('<b>Error '.__METHOD__.' : Database with id "'.$id.'" does not exist in database pool.<br />'.PHP_EOL);
        return self::$databases[$id]->getInstance();
    }

    public static function getDatabaseById($id): ?self
    {
        return self::$databases[$id];
    }

    private function __clone()
    {
        throw new Exception('<b>Error '.__METHOD__.' : You shall not clone.<br />'.PHP_EOL);
    }

    public function __destruct()
    {
        foreach(self::$PDOInstances as $PDOInstance) {
            $PDOInstance = null;
            unset($PDOInstance);
        }
    }
}
