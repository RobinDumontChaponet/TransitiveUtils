<?php

namespace Transitive\Utils;

use PDO;
use PDOStatement;

class Statement extends PDOStatement {
    public $pdo;

    private $where = '';
    private $parameters = array();

    private $o;

    protected function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

	public function execute($inputParameters = null)
	{
		if(!empty($this->where) && !empty($this->queryString)) {
			$this->o = $this->pdo->prepare($this->getQuery());
		}

/*
		file_put_contents(LOG.'queries.log', $this->getQuery().PHP_EOL, FILE_APPEND);
		if(!empty($this->parameters))
			file_put_contents(LOG.'queries.log', json_encode($this->parameters).PHP_EOL, FILE_APPEND);
*/

		if(empty($inputParameters)) {
			if($this->o) {
				foreach($this->parameters as $parameter => $value)
					$this->o->bindValue($parameter, $value);

				$this->o->execute();
			} else
				parent::execute();
		} else
			if($this->o)
				$this->o->execute($inputParameters+$this->parameters);
			else
				parent::execute($inputParameters+$this->parameters);
	}

	public function getQuery() {
		return $this->queryString . ((!empty($this->where) && !empty($this->queryString))? ' WHERE'.$this->where : '');
	}

	public function bindClause(string $parameter, $value, string $sql)
	{
		$this->parameters[$parameter] = $value;
		$this->where.= ' ' . ((!empty($this->where))?'OR ':'') . $sql;
	}

	public function getStatement()
	{
		return $this->o ?? $this;
	}
}