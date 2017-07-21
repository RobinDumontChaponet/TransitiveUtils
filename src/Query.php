<?php

namespace Transitive\Utils;

use PDO;
use PDOStatement;

class Query {
    private $where = '';
    private $parameters = array();

    private $sql;

    protected function __construct(string $sql)
    {
        $this->sql = $sql;
    }

	public function execute($inputParameters = null)
	{
		if(empty($inputParameters)) {
			foreach($this->parameters as $parameter => $value)
				$this->bindValue($parameter, $value);

			if(!empty($this->where) && !empty($this->queryString))
				$this->queryString .= ' WHERE'.$this->where;

			parent::execute();
		}
		else
			parent::execute($inputParameters+$this->parameters);

		file_put_contents(LOG.'out.log', $this->queryString.PHP_EOL, FILE_APPEND);
	}

	public function getQuery() {
		return $this->queryString . ((!empty($this->where) && !empty($this->queryString))? $this->queryString .= ' WHERE'.$this->where : '');
	}

	public function bindClause(string $parameter, $value, string $sql)
	{
		$this->parameters[$parameter] = $value;
		$this->where.= ' ' . ((!empty($this->where))?'OR ':'') . $sql;
	}
}