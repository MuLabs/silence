<?php
namespace Beable\Kernel\Db\PDO;

use Beable\Kernel;

class Handler extends Kernel\Db\Handler
{
	private $typeToSQL = array(
		'tinyint' => 'TINYINT',
		'smallint' => 'SMALLINT',
		'mediumint' => 'MEDIUMINT',
		'int' => 'INT',
		'bigint' => 'BIGINT',
		'bool' => 'TINYINT',
		'string' => 'VARCHAR',
		'varchar' => 'VARCHAR',
		'char' => 'CHAR',
		'blob' => 'BLOB',
		'text' => 'TEXT',
		'long_blob' => 'LONG_BLOB',
		'long_text' => 'LONG_TEXT',
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

		if (!$statement->execute()) {
			$message = $statement->errorInfo()[2];
			throw new Exception(print_r($message, true), Exception::QUERY_FAIL);
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
				$result = $this->getLink()->query($query);
				$time_b = microtime(true);
				$this->getApp()->getDatabase()->log(
					array(
						'query' => $query,
						'time' => $time_b - $time_a
					)
				);
			} else {
				$result = $this->getLink()->exec($query);
			}

			return $result;
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
		return $this->getLink()->errorCode() . ' - ' . $this->getLink()->errorInfo();
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
		return $this->getLink()->lastInsertId();
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
