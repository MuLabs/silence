<?php
namespace Mu\Kernel\Model;

use Mu\Kernel;

abstract class Manager extends Kernel\Core implements Kernel\Db\Interfaces\Requestable, Kernel\Model\Interfaces\Manager
{
	use Kernel\Db\Traits\Requestable;

	protected $defaultScope = Kernel\Cache\Handler\Core::SCOPE_ALL;
	protected $entities = array();
	protected $entityType;
	protected $name;
	protected $forceGet = false;

	protected $entityClassname;

	/**
	 * @return bool
	 */
	public function initialize()
	{
		// Allow to do an initialisation if needed (after Application registration)
		return true;
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
	 * @param Entity[] $entities
	 * @return Entity[]
	 */
	abstract public function initEntities(array $entities);

	/**
	 * @param array $idList
	 * @param bool $keepNull
	 * @return Entity[]
	 */
	public function multiGet(array $idList, $keepNull = false)
	{
		$cacheKeys = array();
		$entities = array_flip($idList);
		// Generate cache key list and check local cache elements
		foreach ($idList as $id) {
			$cacheKey = $this->getCacheKey($id);

			if (isset($this->entities[$id])) {
				$entities[$id] = $this->entities[$id];
				continue;
			}
			$cacheKeys[$id] = $cacheKey;
		}
		unset($idList);

		// Check object in entities cache system
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

		// Create missing items list
		$toRetrieve = array();
		foreach ($cacheKeys as $id => $cacheKey) {
			if (!isset($cacheEntities[$cacheKey]) || !$cacheEntities[$cacheKey]) {
				$toRetrieve[] = $id;
				continue;
			}

			$cacheEntities[$cacheKey]->setManager($this);
			$this->entities[$id] = $entities[$id] = $cacheEntities[$cacheKey];
		}

		// Get missing items
		if (is_array($toRetrieve) && count($toRetrieve)) {
			$toRetrieve = $this->initEntities($toRetrieve);
			foreach ($toRetrieve as $id => $entity) {
				if (!$entity->isValid()) {
					$entity = null;
				} else {
					$entity->initialize();
				}

				if ($entityCache && $entity) {
					$entityCache->set($entity, $this->getDefaultScope());
				}
				$this->entities[$id] = $entities[$id] = $entity;
			}
			unset($toRetrieve);
		}

		// Purge nulls if not allowed
		if (!$keepNull) {
			foreach ($entities as $id => $value) {
				if ($value === null) {
					unset($entities[$id]);
				}
			}
		}

		return $entities;
	}

	/**
	 * @param mixed $id
	 * @param bool $force
	 * @return Entity
	 */
	public function get($id, $force = false)
	{
		if (empty($id)) {
			return null;
		}

		$force = $this->getForceGet();
		$key = $this->getCacheKey($id);
		if (isset($this->entities[$key]) && !$force) {
			return $this->entities[$key];
		}

		$entity = false;
		$entityCache = $this->getApp()->getEntityCache();

		if ($entityCache && !$force) {
			try {
				$entity = $entityCache->get($key, $this->getDefaultScope());
			} catch (Kernel\Cache\Exception $e) {
				// If object not found in cache, just generate it
			}
		}

		if ($entity) {
			$entity->setManager($this);
		} else {
			/** @var Entity $entity */
			$result = $this->initEntities(array($id));
			if (!count($result)) {
				$entity = null;
			} else {
				$entity = reset($result);
				if (!$entity->isValid()) {
					$entity = null;
				} else {
					$entity->initialize();
				}
			}
		}

		if ($entityCache && $entity) {
			$entityCache->set($entity, $this->getDefaultScope());
		}
		$this->entities[$key] = $entity;

		return $entity;
	}

	/**
	 * @param int $id
	 * @return Entity
	 */
	protected function generateEntity($id)
	{
		/** @var Entity $entity */
		$classname = $this->getEntityClassname();
		return new $classname($this, $id);
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
	 * @return bool
	 */
	public function getForceGet()
	{
		return $this->forceGet;
	}

	/**
	 * @param bool $force
	 */
	public function setForceGet($force)
	{
		$this->forceGet = (bool)$force;
	}

	/**
	 * @param int $id
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
	 * @return string
	 */
	public function getSpecificWhere()
	{
		return ':' . $this->getMainProperty() . ' = ?';
	}

	/**
	 * Get the title field of a property key
	 * @param string $key
	 * @return mixed
	 */
	public function translateProperties($key)
	{
		// Replace _ by camelcase
		if (strpos($key, '_') !== false) {
			$finalKey = '';
			$keyLen = strlen($key);
			$_last = false;
			for ($i = 0; $i < $keyLen; ++$i) {
				if ($key[$i] == '_') {
					$_last = true;
				} elseif ($_last) {
					$_last = false;
					$finalKey .= strtoupper($key[$i]);
				} else {
					$finalKey .= $key[$i];
				}
			}
			$key = $finalKey;
		}


		if (isset($this->properties[$this->getDefaultGroup()]['properties'][$key]['title'])) {
			return $this->properties[$this->getDefaultGroup()]['properties'][$key]['title'];
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

	public function discard()
	{
		$pageCacheManage = $this->getApp()->getPageCache();
		if ($pageCacheManage) {
			// Discard manager cache page
			$pageCacheManage->delete('*{' . $this->getEntityType() . '}*');
		}
	}
}