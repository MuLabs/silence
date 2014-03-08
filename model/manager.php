<?php
namespace Mu\Kernel\Model;

use Mu\Kernel;

abstract class Manager extends Kernel\Core implements Kernel\Db\Interfaces\Requestable, Kernel\Model\Interfaces\Manager
{
	use Kernel\Db\Traits\Requestable;

	private $defaultScope = Kernel\Cache\Handler\Core::SCOPE_ALL;
	private $entities = array();
	private $entityType;
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
}