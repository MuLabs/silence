<?php
namespace Mu\Kernel\Model;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	private $managers = array();
	protected $allowedEntities = array();
	protected $entityClassName = array();

	/**
	 * @param string $classAlias
	 * @return Manager
	 */
	public function getOneManager($classAlias)
	{
		$classAlias = strtolower($classAlias);
		$entityType = $this->getEntityTypeFromAlias($classAlias);
		if (!isset($this->managers[$entityType])) {
			$fullName = $this->entityClassName[$classAlias];

			/** @var Manager $manager */
			$manager = $this->getApp()->getFactory()->get($fullName);
			$manager->setEntityType($entityType);
			$manager->setName($classAlias);
			$manager->initialize();
			$this->managers[$entityType] = $manager;
		}

		return $this->managers[$entityType];
	}

	/**
	 * @param int $entityType
	 * @param string $classAlias
	 * @param string|bool $bundleName
	 */
	public function registerEntity($entityType, $classAlias, $bundleName = false)
	{
		// Check bundle existence
		if ($bundleName) {
			$this->getApp()->getBundler()->get($bundleName);
		}

		if ($bundleName === false) {
			$className = '\\Mu\\App\\Model\\Manager\\' . $classAlias;
		} else {
			$className = '\\Mu\\Bundle\\' . $bundleName . '\\Model\\Manager\\' . $classAlias;
		}

		$classAlias = strtolower($classAlias);
		$this->allowedEntities[$entityType] = $classAlias;
		$this->entityClassName[$classAlias] = $className;
	}

	/**
	 * @param string $stdOut
	 * @return bool
	 */
	public function createStructure($stdOut = '\print')
	{
		foreach ($this->allowedEntities as $classAlias) {
			$entityManager = $this->getOneManager($classAlias);
			$entityManager->createStructure($stdOut);
		}
	}

	/**
	 * @param string $stdOut
	 * @return bool
	 */
	public function createDefaultDataSet($stdOut = '\print')
	{
		foreach ($this->allowedEntities as $classAlias) {
			$entityManager = $this->getOneManager($classAlias);
			$entityManager->createDefaultDataSet($stdOut);
		}
	}

	/**
	 * @param $entityType
	 * @return Manager
	 */
	public function getManagerFromType($entityType)
	{
		return $this->getOneManager($this->getEntityAliasFromType($entityType));
	}

	/**
	 * @param string $classAlias
	 * @return int
	 * @throws Exception
	 */
	public function getEntityTypeFromAlias($classAlias)
	{
		$tmpArray = array_flip($this->allowedEntities);
		$classAlias = strtolower($classAlias);

		if (!isset($tmpArray[$classAlias])) {
			throw new Exception($classAlias, Exception::INVALID_ENTITY_CLASSNAME);
		}

		return $tmpArray[$classAlias];
	}

	/**
	 * @param int $entityType
	 * @throws Exception
	 * @return string
	 */
	public function getEntityAliasFromType($entityType)
	{
		if (!isset($this->allowedEntities[$entityType])) {
			throw new Exception($entityType, Exception::INVALID_ENTITY_TYPE);
		}

		return $this->allowedEntities[$entityType];
	}

	/**
	 * @param int $entityType
	 * @param int|array $entityId
	 * @return Entity
	 */
	public function getEntityFromTypeAndId($entityType, $entityId)
	{
		if (!$entityId && !$entityType) {
			return null;
		}

		return $this->getOneManager($this->getEntityAliasFromType($entityType))->get($entityId);
	}

	/**
	 * @param string $classAlias
	 * @param int|array $entityId
	 * @return Entity
	 */
	public function getEntityFromAliasAndId($classAlias, $entityId)
	{
		return $this->getOneManager($classAlias)->get($entityId);
	}
}