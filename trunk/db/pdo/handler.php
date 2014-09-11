<?php
namespace Mu\Kernel\Db\PDO;

use Mu\Kernel;

class Handler extends Kernel\Db\Handler
{
	// Query minimal time for log (in ms)
	const QUERY_LOG_LIMIT = 10;

	protected $typeToSQL = array(
		'tinyint' => 'TINYINT',
		'smallint' => 'SMALLINT',
		'mediumint' => 'MEDIUMINT',
		'int' => 'INT',
		'bigint' => 'BIGINT',
		'timestamp' => 'TIMESTAMP',
		'float' => 'FLOAT',
        'double' => 'DOUBLE',
		'bool' => 'TINYINT',
		'string' => 'VARCHAR',
		'varchar' => 'VARCHAR',
		'char' => 'CHAR',
		'blob' => 'BLOB',
		'text' => 'TEXT',
		'long_blob' => 'LONG_BLOB',
		'long_text' => 'LONG_TEXT',
		'date' => 'DATETIME',
		'ip' => 'INT',
	);

	public function __construct($dbDsn, $dbUsername, $dbPass)
	{
		$this->setLink(new \PDO($dbDsn, $dbUsername, $dbPass));
	}

	/**
	 * {@inheritDoc}
	 */
	public function sendQuery(Kernel\Db\Query $query)
	{
		/** @var \PDO $link */
		$this->checkQuery($query);
		$link = $this->getLink();
		$statement = $link->prepare($query->getQuery());
		foreach ($query->getValues() as $offset => $value) {
			$type = $this->transformTypeToPDO($value['type']);
			$statement->bindValue($offset + 1, $value['value'], $type);
		}

		if ($this->hasLogs()) {
			$time_a = microtime(true);
			$result = $statement->execute();
			$time = (microtime(true) - $time_a) * 1000;
			if ($time > self::QUERY_LOG_LIMIT) {
				$this->log(
					__CLASS__,
					array(
						'query' => $query->getQuery(),
						'time' => $time . ' ms',
					)
				);
			}
		} else {
			$result = $statement->execute();
		}

		if (!$result) {
			throw new Exception(print_r($statement->errorInfo(), true), Exception::QUERY_FAIL);
		}


		return new Result($this, $statement);
	}

	/**
	 * @param int $type
	 * @return int
	 */
	private function transformTypeToPDO($type)
	{
		switch ($type) {
            case self::PARAM_INT:
				$value = \PDO::PARAM_INT;
				break;
			default:
			case self::PARAM_STR:
            case self::PARAM_FLOAT:
            $value = \PDO::PARAM_STR;
				break;
			case self::PARAM_BOOL:
				$value = \PDO::PARAM_BOOL;
				break;
		}

		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function protectString($string)
	{
		return $string;
	}

	/**
	 * {@inheritDoc}
	 */
	public function query($query)
	{
		try {
			if ($this->hasLogs()) {
				$time_a = microtime(true);
				$statement = $this->getLink()->query($query);
				$time = (microtime(true) - $time_a) * 1000;
				if ($time > self::QUERY_LOG_LIMIT) {
					$this->log(
						__CLASS__,
						array(
							'query' => $query,
							'time' => $time . ' ms',
						)
					);
				}
			} else {
				$statement = $this->getLink()->query($query);
			}

			if (!$statement) {
				throw new \PDOException();
			}

			return new Result($this, $statement);
		} catch (\PDOException $e) {
			throw new Exception($this->getLastError() . ' == ON == ' . $query, Exception::QUERY_FAIL);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasError()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrors()
	{
		return $this->getLink()->errorCode() . ' - ' . print_r($this->getLink()->errorInfo(), true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastError()
	{
		return $this->getErrors();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInsertId()
	{
		return (int)$this->getLink()->lastInsertId();
	}

	/**
	 * {@inheritDoc}
	 */
	public function startTransaction()
	{
		return $this->getLink()->beginTransaction();
	}

	/**
	 * {@inheritDoc}
	 */
	public function commitTransaction()
	{
		return $this->getLink()->commit();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rollbackTransaction()
	{
		return $this->getLink()->rollBack();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStructureFromProperty(array $property)
	{
		if (!isset($this->typeToSQL[$property['type']])) {
			throw new Exception($property['type'], Exception::INVALID_PROPERTY_TYPE);
		}

		$propDatas = $this->typeToSQL[$property['type']];
		if (isset($property['length'])) {
			$propDatas .= '(' . $property['length'] . ')';
		}

		if (isset($property['pdo_extra'])) {
			$propDatas .= ' ' . $property['pdo_extra'];
		}
		return $propDatas;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStructureFromKey($tableToken, $keyName, array $key)
	{
		switch ($key['type']) {
			case 'primary':
				$keyType = 'PRIMARY KEY';
				break;
			case 'index':
			default:
				$keyType = 'KEY';
				break;
			case 'unique':
				$keyType = 'UNIQUE KEY';
				break;
			case 'fulltext':
				$keyType = 'FULLTEXT';
				break;
		}

		$keyProperties = array();
		foreach ($key['properties'] as $oneKeyProp) {
			$keyProperties[] = $tableToken . '.' . $oneKeyProp;
		}
		return $keyType . ' `' . $keyName . '` (' . implode(', ', $keyProperties) . ')';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStructureFromTableInfos(array $tableInfos)
	{
		$charset = (isset($tableInfos['charset'])) ? $tableInfos['charset'] : 'utf8';
		$engine = (isset($tableInfos['engine'])) ? $tableInfos['engine'] : 'InnoDB';

		return 'ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset;
	}
}
