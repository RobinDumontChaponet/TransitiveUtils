<?php

namespace Transitive\Utils;

use PDO;
use PDOStatement;

class Statement extends PDOStatement
{
	public $pdo;

	private $where = '';
	private $parameters = array();
	private $limit = '';
	private $offset = '';
	private $orderBy = '';

	private $o;

	private $index = 0;

	private $combinator = 'OR';

	public static $debug = false;
	public static $dump = [];

	protected function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
		$this->o = null;
	}

	public function execute($inputParameters = null)
	{
		if(!$this->o && (!empty($this->where) || !empty($this->limit) || !empty($this->offset)) && !empty($this->queryString))
			$this->o = $this->pdo->prepare($this->getQuery());

		if(self::$debug && !$this->o) {
			ob_start();
			$this->debugDumpParams();
			$dump = ob_get_contents();
			ob_end_clean();

			self::$dump[] = $dump;
		}

		// if(!$this->o) {
		//     file_put_contents(LOG.'queries.log', $this->getQuery().PHP_EOL, FILE_APPEND);
		//     if(!empty($this->parameters))
		//         file_put_contents(LOG.'queries.log', json_encode($this->parameters).PHP_EOL, FILE_APPEND);
		// }

		if(empty($inputParameters)) {
			if($this->o) {
				foreach($this->parameters as $parameter => $value)
					$this->o->bindValue($parameter, $value);

				return $this->o->execute();
			} else
				return parent::execute();
		} elseif($this->o)
			return $this->o->execute($inputParameters + $this->parameters);
		else
			return parent::execute($inputParameters + $this->parameters);
	}

	public function getQuery() {
		return $this->queryString
				.((!empty($this->where) && !empty($this->queryString)) ? ' WHERE'.$this->where : '')
				.((!empty($this->orderBy) && !empty($this->queryString)) ? ' '.$this->orderBy : '')
				.((!empty($this->limit) && !empty($this->queryString)) ? ' '.$this->limit : '')
				.((!empty($this->offset) && !empty($this->queryString)) ? ' '.$this->offset : '');
	}

	public function autoBindClause(string $parameter, $value, string $sql, string $prefix = null, string $suffix = null, string $combinator = null)
	{
		if(!isset($combinator))
			$combinator = $this->combinator;

		if(null !== $value)
			$this->bindClause($parameter, $value, $sql, $prefix, $suffix, $combinator);

		return false;
	}

	public function bindClause(string $parameter, $value, string $sql, string $prefix = null, string $suffix = null, string $combinator = null)
	{
		if(!isset($combinator))
			$combinator = $this->combinator;

		if(is_array($value))
			foreach($value as $key => $item) {
				if(!is_scalar($item))
					$item = $key;

				$this->_bindClause($parameter, $item, $sql, $prefix, $suffix, $combinator);
			}
		else
			$this->_bindClause($parameter, $value, $sql, $prefix, $suffix, $combinator);
	}

	private function _bindClause(string $parameter, $value, string $sql, string $prefix = null, string $suffix = null, string $combinator = null)
	{
		if(!isset($combinator))
			$combinator = $this->combinator;

		$this->parameters[$parameter.'_'.$this->index] = $prefix.$value.$suffix;
		$this->where .= ' '.((!empty($this->where)) ? $combinator.' ' : '').'('.str_replace($parameter, $parameter.'_'.$this->index++, $sql).')';
	}

	public function getStatement()
	{
		return $this->o ?? $this;
	}

	public function setLimit(int $limit = null)
	{
		if(is_integer($limit) && $limit > 0) {
			$this->limit = 'LIMIT :limit';
			$this->parameters[':limit'] = $limit;
		} else
			$this->limit = '';
	}

	public function setOffset(int $offset = null)
	{
		if(is_integer($offset) && $offset > 0) {
			$this->offset = ($offset) ? 'OFFSET :offset' : '';
			$this->parameters[':offset'] = $offset;
		} else
			$this->offset = '';
	}

	public function orderBy(string $column = null, string $direction = null)
	{
		if($column) {
			$column = htmlspecialchars(htmlentities(strip_tags(addcslashes($column, '%_')), ENT_NOQUOTES, 'UTF-8'));

			$this->orderBy = 'ORDER BY '.$column;
			if(isset($direction))
				$this->orderBy .= ('desc' == strtolower($direction)) ? ' DESC' : ' ASC';
		} else
			$this->orderBy = '';
	}

	public function appendSQL(string $sql)
	{
		$this->where .= ' '.$sql;
	}

	public function addParam(string $key, $value)
	{
		$this->parameters[$key] = $value;
	}

	public function setCombinator(string $combinator = 'OR')
	{
		$this->combinator = $combinator;
	}
}
