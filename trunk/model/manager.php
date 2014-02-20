<?php
namespace Mu\Kernel\Model;

use Mu\Kernel;

abstract class Manager extends Kernel\Core
{
	private $defaultScope = Kernel\Cache\Handler\Core::SCOPE_ALL;
	private $entities = array();
	protected $properties = array();
	private $entityType;
	private $dbHandler;
	private $name;

	private $entityClassname;

	/**
	 * @return bool
	 */
	public function initialize()
	{
		// Allow to do an initialisation if needed (after Application registration)
		return true;
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @param array $idList
	 * @param bool $keep_null
	 * @return Entity[]
	 */
	public function multiGet(array $idList, $keep_null = false)
	{
		$cacheKeys = array();
		$entities = array();
		foreach ($idList as $key => $id) {
			$cacheKey = $this->getCacheKey($idList[$key]);

			if (isset($this->entities[$cacheKey])) {
				$entities[$key] = $this->entities[$cacheKey];
				continue;
			}
			$cacheKeys[$key] = $cacheKey;
		}

		$entityCache = $this->getApp()->getEntityCache();
		/** @var Entity[] $cacheEntities */
		$cacheEntities = array();
		if ($entityCache) {
			try {
				$cacheEntities = $entityCache->multiGet($cacheKeys, $this->getDefaultScope());
			} catch (Kernel\Cache\Exception $e) {
				// If objects not found, just generate them
			}
		}

		foreach ($cacheKeys as $key => $cacheKey) {
			if (!isset($cacheEntities[$cacheKey]) || !$cacheEntities[$cacheKey]) {
				$cacheEntities[$cacheKey] = $this->generateEntity($idList[$key]);
			}

			if ($entityCache) {
				$entityCache->set($cacheEntities[$cacheKey], $this->getDefaultScope());
			}
			$this->entities[$cacheKey] = $entities[$key] = $cacheEntities[$cacheKey];
		}

		// Purge nulls if not allowed
		if (!$keep_null) {
			foreach ($entities as $key => $value) {
				if ($value === null) {
					unset($entities[$key]);
				}
			}
		}

		return $entities;
	}

	/**
	 * @param mixed $id
	 * @return Entity
	 */
	public function get($id)
	{
		if (empty($id)) {
			return null;
		}

		$key = $this->getCacheKey($id);
		if (isset($this->entities[$key])) {
			return $this->entities[$key];
		}

		$entity = false;
		$entityCache = $this->getApp()->getEntityCache();

		if ($entityCache) {
			try {
				$entity = $entityCache->get($key, $this->getDefaultScope());
			} catch (Kernel\Cache\Exception $e) {
				// If object not found in cache, just generate it
			}
		}

		if (!$entity) {
			$entity = $this->generateEntity($id);
		}

		if ($entityCache) {
			$entityCache->set($entity, $this->getDefaultScope());
		}
		$this->entities[$key] = $entity;

		return $entity;
	}

	/**
	 * @param array $id
	 * @return Kernel\Model\Entity
	 */
	private function generateEntity($id)
	{
		/** @var Entity $entity */
		$classname = $this->getEntityClassname();
		$entity = new $classname($this, $id);

		if (!$entity->isValid()) {
			$entity = null;
		}

		return $entity;
	}

	/**
	 * @param string $stdOut
	 * @return bool
	 */
	public function createStructure($stdOut = '\print')
	{
		$handler = $this->getDbHandler();
		$entityClassname = strtolower($this->getEntityClassname());
		call_user_func($stdOut, 'Start creating structure for entity ' . $entityClassname);
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

		call_user_func($stdOut, $entityClassname . ' => Done');
		return true;
	}

	/**
	 * @param string $stdOut
	 * @return bool
	 */
	public function createDefaultDataSet($stdOut = '\print')
	{
		$entityClassname = strtolower($this->getEntityClassname());
		call_user_func($stdOut, $entityClassname . ' => No DataSet Found');

		return true;
	}

	/**
	 * @return int
	 */
	public function getDefaultScope()
	{
		return $this->defaultScope;
	}

	/**
	 * @param int $scope
	 */
	public function setDefaultScope($scope)
	{
		$this->defaultScope = (int)$scope;
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
	 * @param array $id
	 * @return string
	 */
	public function getCacheKey($id)
	{
		return $this->getEntityClassname() . '|' . $id;
	}

	/**
	 * @return string
	 */
	public function getEntityClassname()
	{
		if (is_null($this->entityClassname)) {
			$this->entityClassname = str_replace('\\Model\\Manager\\', '\\Model\\Entity\\', get_called_class());
		}
		return $this->entityClassname;
	}

	/**
	 * @param int $entityType
	 */
	public function setEntityType($entityType)
	{
		$this->entityType = (int)$entityType;
	}

	/**
	 * @return int
	 */
	public function getEntityType()
	{
		return $this->entityType;
	}

	/**
	 * @param string $group
	 * @param string $name
	 * @throws Exception
	 * @return array
	 */
	public function getProperty($group, $name)
	{
		if (!isset($this->properties[$group]['properties'][$name])) {
			throw new Exception($group . ' - ' . $name, Exception::INVALID_PROPERTY);
		}
		return $this->properties[$group]['properties'][$name];
	}

	/**
	 * @param string $group
	 * @return array
	 * @throws Exception
	 */
	public function getGroupPropertyInfos($group)
	{
		if (!isset($this->properties[$group])) {
			throw new Exception($group, Exception::INVALID_PROPERTY_GROUP);
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
	 * @return string
	 */
	public function getSpecificWhere()
	{
		return ':' . $this->getMainProperty() . ' = ?';
	}

	/**
	 * @return Kernel\Db\Handler
	 * @throws Exception
	 */
	public function getDbHandler()
	{
		$app = $this->getApp();
		if (!isset($this->dbHandler)) {
			$this->setDbHandler($app->getDatabase()->getHandler($app->getDefaultDbContext()));
		}

		if (!$this->dbHandler) {
			throw new Exception(__CLASS__, Exception::NO_DB_HANDLER);
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

	/**
	 * Get the title field of a property key
	 * @param string $key
	 * @return mixed
	 */
	public function translateProperties($key)
	{
		if (isset($this->properties[$key]['title'])) {
			return $this->properties[$key]['title'];
		}
		return $key;
	}

	/**
	 * @return string
	 */
	public function getMainProperty()
	{
		return 'id';
	}
}