<?php
namespace Beable\Kernel\Db;

use Beable\Kernel;

abstract class Handler extends Kernel\Core
{
	const PARAM_INT = 1;
	const PARAM_STR = 2;
	const PARAM_BOOL = 3;

	protected $knowActions = array(
		'tinyint' => self::PARAM_INT,
		'smallint' => self::PARAM_INT,
		'mediumint' => self::PARAM_INT,
		'int' => self::PARAM_INT,
		'bigint' => self::PARAM_INT,
		'bool' => self::PARAM_BOOL,
		'string' => self::PARAM_STR,
		'varchar' => self::PARAM_STR,
		'char' => self::PARAM_STR,
		'blob' => self::PARAM_STR,
		'text' => self::PARAM_STR,
		'long_blob' => self::PARAM_STR,
		'long_text' => self::PARAM_STR,
	);
	protected $typeCheckValues = array(
		Query::TYPE_SELECT,
		Query::TYPE_UPDATE,
	);

	private $link;
	private $hasLogs = false;

	/**
	 * @param bool $enable
	 */
	public function enableLogs($enable = true)
	{
		$this->hasLogs = (bool)$enable;
	}

	/**
	 * @return bool
	 */
	public function hasLogs()
	{
		return $this->hasLogs;
	}

	/**
	 * @return mixed
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * @param mixed $link
	 */
	public function setLink($link)
	{
		$this->link = $link;
	}


	/**
	 * @param string $string
	 * @return string
	 */
	abstract protected function protectString($string);

	/**
	 * Check if query is valid
	 *
	 * @param Query $query
	 * @throws Exception
	 * @return bool
	 */
	protected function checkQuery(Query $query)
	{
		$strQuery = $query->getquery();
		$checkValue = in_array($query->gettype(), $this->typeCheckValues);

		#region value check analysis
		$values = $query->getvalues();
		$valuesOffset = count($values) - 1;
		$lastFound = strrpos($strQuery, '?');
		$subQuery = $strQuery;
		while ($lastFound !== false && $values >= 0) {
			$subQuery = substr($subQuery, 0, $lastFound);
			$subPropQuery = substr($subQuery, strrpos($subQuery, ':') + 1);
			$subPropQuery = substr($subPropQuery, 0, strpos($subPropQuery, ' '));
			if (!$subPropQuery) {
				throw new exception($subQuery, exception::INVALID_SUB_PROP_QUERY);
			}
			$subPropQuery = str_replace(
				array(')', ',', ';', '(', '/', '\\'),
				'',
				$subPropQuery);

			$property = explode('.', $subPropQuery);
			$propertyCount = count($property);
			if ($propertyCount === 3) {
				$property = array(
					'manager' => explode('-', $property[0]),
					'group' => $property[1],
					'property' => $property[2],
				);
				$property['manager'] = $this->getManager($property);

			} elseif ($propertyCount === 2) {
				$property['manager'] = $query->getDefaultManager();
				$property['group'] = reset($property);
				$property['property'] = next($property);
			} elseif ($propertyCount === 1) {
				$property['manager'] = $query->getDefaultManager();
				$property['group'] = $query->getDefaultManager()->getDefaultGroup();
				$property['property'] = reset($property);
			} else {
				throw new exception(array(
					'query' => $strQuery,
					'property' => $property
				), exception::INVALID_PROPERTY_COUNT);
			}

			$property = $property['manager']->getproperty($property['group'], $property['property']);
			$propertyType = $this->knowActions[$property['type']];
			$values[$valuesOffset] = array(
				'type' => $propertyType,
				'value' => ($checkValue ? $this->checkproperty(
						$values[$valuesOffset],
						$this->knowActions[$property['type']]
					) : $values[$valuesOffset])
			);

			$lastFound = strrpos($subQuery, '?');
			--$valuesOffset;
		}
		$query->setvalues($values);
		#endregion

		$propertyPattern = '(?<property>[\w]+)';
		$groupPattern = '(?<group>[\w]+)';
		$managerPattern = '(?<manager>[\w]+)';

		// replace all properties by their real name
		#region :manager.group.property
		while (preg_match(
			'#:' . $managerPattern . '\.' . $groupPattern . '\.' . $propertyPattern . '#i',
			$strQuery,
			$property
		)) {
			$strQuery = preg_replace(
				'#:' . $property['manager'] . '.' . $property['group'] . '.' . $property['property']. '(/[^\w\-])#i',
				$this->getManager($property)->getPropertyForDb($property['group'], $property['property'], $query->isShortMode()) . '$1',
				$strQuery, 1
			);
		}
		#endregion

		#region :group.property
		while (preg_match(
			'#:' . $groupPattern . '\.' . $propertyPattern . '#i',
			$strQuery,
			$property
		)) {
			$property['manager'] = $query->getDefaultManager();
			$strQuery = preg_replace(
				'#:' . $property['group'] . '.' . $property['property'].'([^\w\-])#i',
				$property['manager']->getPropertyForDb($property['group'], $property['property'], $query->isShortMode()) . '$1',
				$strQuery, 1
			);
		}
		#endregion

		#region :property
		while (preg_match(
			'#:' . $propertyPattern . '#i',
			$strQuery,
			$property
		)) {
			$property['manager'] = $query->getDefaultManager();
			$property['group'] = $query->getDefaultManager()->getDefaultGroup();
			$strQuery = preg_replace(
				'#:' . $property['property']. '([^\w\-])#i',
				$property['manager']->getPropertyForDb($property['group'], $property['property'], $query->isShortMode()) . '$1',
				$strQuery, 1
			);
		}
		#endregion

		#region @manager.group
		while (preg_match('#@' . $managerPattern . '\.' . $groupPattern . '#i', $strQuery, $property)) {
			$strQuery = preg_replace(
				'#@' . $property['manager'] . '.' . $property['group']. '(/[^\w\-])#i',
				$this->getManager($property)->getGroupForDb($property['group']) . '$1',
				$strQuery, 1
			);
		}
		#endregion

		#region @group
		while (preg_match('#@' . $groupPattern . '#i', $strQuery, $property)) {
			$property['manager'] = $query->getDefaultManager();
			$strQuery = preg_replace(
				'#@' . $property['group']. '([^\w\-])#i',
				$property['manager']->getGroupForDb($property['group']) . '$1',
				$strQuery, 1
			);
		}
		#endregion

		#region @
		while (preg_match('#@#i', $strQuery, $property)) {
			$property['manager'] = $query->getDefaultManager();
			$property['group'] = $query->getDefaultManager()->getDefaultGroup();
			$strQuery = preg_replace(
				'#@([^\w\-])#i',
				$property['manager']->getGroupForDb($property['group'], $query->isShortMode()) . '$1',
				$strQuery, 1
			);
		}
		#endregion

		$query->setQuery($strQuery);
	}

	/**
	 * @param mixed $value
	 * @param int $type
	 * @return int|string
	 */
	private function checkProperty($value, $type)
	{
		switch ($type) {
			case self::PARAM_INT:
				return (int)$value;
				break;
			case self::PARAM_STR:
				return $this->protectString($value);
				break;
			case self::PARAM_BOOL:
				return (int)(bool)$value;
				break;
			default:
				return $value;
				break;
		}
	}

	/**
	 * @param array $property
	 * @return Kernel\Model\Manager
	 * @throws Exception
	 */
	private function getManager(array $property)
	{
		if (!isset($property['manager'])) {
			throw new Exception(print_r($property, true), Exception::INVALID_MANAGER);
		}
		return $this->getApp()->getModelManager()->getOneManager($property['manager'][0]);
	}

	/**
	 * @return bool
	 */
	public abstract function startTransaction();

	/**
	 * @return bool
	 */
	public abstract function commitTransaction();

	/**
	 * @return bool
	 */
	public abstract function rollbackTransaction();

	/**
	 * @param string $query
	 * @return mixed
	 */
	public abstract function query($query);

	/**
	 * @param Query $query
	 * @return Result
	 */
	public abstract function sendQuery(Kernel\Db\Query $query);

	/**
	 * @return bool
	 */
	public abstract function hasError();

	/**
	 * @return string
	 */
	public abstract function getErrors();

	/**
	 * @return string
	 */
	public abstract function getLastError();

	/**
	 * @return int
	 */
	public abstract function getInsertId();

	/**
	 * @param array $property
	 * @return string
	 */
	public abstract function getStructureFromProperty(array $property);

	/**
	 * @param string $tableToken
	 * @param string $keyName
	 * @param array $key
	 * @return string
	 */
	public abstract function getStructureFromKey($tableToken, $keyName, array $key);

	/**
	 * @param array $tableInfos
	 * @return string
	 */
	public abstract function getStructureFromTableInfos(array $tableInfos);
}
