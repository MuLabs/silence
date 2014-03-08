<?php
namespace Mu\Kernel\Model\Interfaces;

use Mu\Kernel;

interface Entity extends Kernel\CoreInterface
{
	/**
	 * @return bool
	 */
	public function isInitialized();

	/**
	 * @return bool
	 */
	public function isValid();

#region Getters
	/**
	 * @return Manager
	 */
	public function getManager();

	/**
	 * @return int
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getCacheKey();

	/**
	 * @return int
	 */
	public function getEntityType();
#endregion

#region Setters
	/**
	 * @param Manager $manager
	 */
	public function setManager(Manager $manager);

	/**
	 * @param int $id
	 * @return void
	 */
	public function setId($id);

	/**
	 * @return bool
	 */
	public function save();

	/**
	 * @return bool
	 */
	public function delete();

	/**
	 * @return bool
	 */
	public function remove();
#endregion


	public function discard();

	/**
	 * @return string
	 */
	public function __toString();

	/**
	 * @return string
	 */
	public function jsonSerialize();

	/**
	 * @param int $action
	 * @param array $oldValue
	 * @param array $newValue
	 * @return bool
	 */
	public function logAction($action, $oldValue, $newValue);

	/**
	 * @return array
	 */
	public function getLogsList();

	/**
	 * @param $key
	 * @return mixed|string
	 */
	public function getPropertyValue($key);

	/**
	 * @param string $property
	 * @param string $lang
	 * @return string
	 */
	public function getLocalizedValue($property, $lang = null);

	/**
	 * @param string $property
	 * @param string $lang
	 * @param mixed $value
	 * @return bool
	 */
	public function setLocalizedValue($property, $lang, $value);

	/**
	 * @param $key
	 * @param $value
	 * @return string
	 */
	public function toStringValue($key, $value);
}