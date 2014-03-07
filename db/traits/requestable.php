<?php
namespace Mu\Kernel\Db\Traits;

use Mu\Kernel;

trait Requestable
{
	use Kernel\CoreTrait;

	protected $properties = array();
	private $dbHandler;

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @param string $stdOut
	 * @return bool
	 */
	public function createStructure($stdOut = '\print')
	{
		$handler = $this->getDbHandler();
		call_user_func($stdOut, 'Start creating structure for ' . get_called_class());
		foreach ($this->properties as $table => $oneTableInfos) {
			$properties = array();
			$tableToken = '@' . $table;
			call_user_func($stdOut, 'Start creating structure for table ' . $tableToken);

			// Generate properties
			foreach ($oneTableInfos['properties'] as $label => $oneProperty) {
				if (!isset($oneProperty['db'])) {
					continue;
				}

				$propToken = str_replace('@', ':', $tableToken) . '.' . $label;
				call_user_func($stdOut, 'Generating property ' . $propToken);

				$propDatas = $handler->getStructureFromProperty($oneProperty);
				$properties[] = $propToken . ' ' . $propDatas;
				call_user_func($stdOut, $propToken . ' => Done');
			}

			// Generate keys
			foreach ($oneTableInfos['keys'] as $keyName => $oneKey) {
				call_user_func($stdOut, 'Generating key ' . $keyName);

				$properties[] = $handler->getStructureFromKey(str_replace('@', ':', $tableToken), $keyName, $oneKey);
				call_user_func($stdOut, $keyName . ' => Done');
			}

			// Generate tables infos
			$tableExtra = $handler->getStructureFromTableInfos($oneTableInfos);
			$query = new Kernel\Db\Query('CREATE TABLE ' . $tableToken . ' (' . implode(
					', ',
					$properties
				) . ') ' . $tableExtra, array(), $this);
			$query->setShortMode(true);
			$handler->sendQuery($query);

			call_user_func($stdOut, $tableToken . ' => Done');
		}

		call_user_func($stdOut, get_called_class() . ' => Done');
		return true;
	}

	/**
	 * @param string $stdOut
	 * @return bool
	 */
	public function createDefaultDataSet($stdOut = '\print')
	{
		call_user_func($stdOut, get_called_class() . ' => No DataSet Found');

		return true;
	}

	/**
	 * @return string
	 */
	public function getDefaultGroup()
	{
		foreach ($this->properties as $key => $value) {
			return $key;
		}

		return '';
	}

	/**
	 * @param string $group
	 * @param string $name
	 * @throws Kernel\Db\Exception
	 * @return array
	 */
	public function getProperty($group, $name)
	{
		if (!isset($this->properties[$group]['properties'][$name])) {
			throw new Kernel\Db\Exception($group . ' - ' . $name, Kernel\Db\Exception::INVALID_PROPERTY);
		}
		return $this->properties[$group]['properties'][$name];
	}

	/**
	 * @param string $group
	 * @return array
	 * @throws Kernel\Db\Exception
	 */
	public function getGroupPropertyInfos($group)
	{
		if (!isset($this->properties[$group])) {
			throw new Kernel\Db\Exception($group, Kernel\Db\Exception::INVALID_PROPERTY_GROUP);
		}
		return $this->properties[$group]['infos'];
	}

	/**
	 * @param string $group
	 * @param string $name
	 * @param bool $isShortMode
	 * @return string
	 */
	public function getPropertyForDb($group, $name, $isShortMode = false)
	{
		$propertyInfos = $this->getProperty($group, $name);
		if ($isShortMode) {
			return '`' . $propertyInfos['db'] . '`';
		} else {
			$groupInfos = $this->getGroupPropertyInfos($group);
			return '`' . $groupInfos['db'] . '`.`' . $propertyInfos['db'] . '`';
		}
	}

	/**
	 * @param string $group
	 * @return string
	 */
	public function getGroupForDb($group)
	{
		$groupInfos = $this->getGroupPropertyInfos($group);
		return '`' . $groupInfos['db'] . '`';
	}

	/**
	 * @return Kernel\Db\Handler
	 * @throws Kernel\Db\Exception
	 */
	public function getDbHandler()
	{
		$app = $this->getApp();
		if (!isset($this->dbHandler)) {
			$this->setDbHandler($app->getDatabase()->getHandler($app->getDefaultDbContext()));
		}

		if (!$this->dbHandler) {
			throw new Kernel\Db\Exception(__CLASS__, Kernel\Db\Exception::NO_DB_HANDLER);
		}

		return $this->dbHandler;
	}

	/**
	 * @param Kernel\Db\Handler $handler
	 */
	public function setDbHandler(Kernel\Db\Handler $handler)
	{
		$this->dbHandler = $handler;
	}
}