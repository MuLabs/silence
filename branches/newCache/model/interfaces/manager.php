<?php
namespace Mu\Kernel\Model\Interfaces;

use Mu\Kernel;

interface Manager extends Kernel\CoreInterface
{
	/**
	 * @return bool
	 */
	public function initialize();

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @param string $name
	 */
	public function setName($name);

	/**
	 * @param array $idList
	 * @param bool $keep_null
	 * @return Entity[]
	 */
	public function multiGet(array $idList, $keep_null = false);

	/**
	 * @param mixed $id
	 * @return Entity
	 */
	public function get($id);

	/**
	 * @return int
	 */
	public function getDefaultScope();

	/**
	 * @param int $scope
	 */
	public function setDefaultScope($scope);

	/**
	 * @param array $id
	 * @return string
	 */
	public function getCacheKey($id);

	/**
	 * @return string
	 */
	public function getEntityClassname();

	/**
	 * @param int $entityType
	 */
	public function setEntityType($entityType);

	/**
	 * @return int
	 */
	public function getEntityType();

	/**
	 * @return string
	 */
	public function getSpecificWhere();

	/**
	 * Get the title field of a property key
	 * @param string $key
	 * @return mixed
	 */
	public function translateProperties($key);

	/**
	 * @return string
	 */
	public function getMainProperty();
}