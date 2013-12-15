<?php
namespace Beable\Kernel\Model;

use Beable\Kernel;

abstract class Entity extends Kernel\Core implements \JsonSerializable
{
	protected $manager;
	protected $mainId = array();
	protected $unsavedChanges = array();
	protected $initialValues = array();
	protected $id;

#region Initialization
	/**
	 * @param Manager $manager
	 * @param $id
	 * @throws Exception
	 */
	final public function __construct(Manager $manager, $id)
	{
		$this->setApp($manager->getApp());
		$this->setManager($manager);
		$this->setMainId($id);

		$this->initialize();
	}

	protected abstract function initialize();

#endregion Initialization

	public function isValid()
	{
		$mainId = $this->getMainId();
		return !empty($mainId);
	}

#region Getters
	/**
	 * @return Manager
	 */
	public function getManager()
	{
		return $this->manager;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		if (is_null($this->id)) {
			$this->id = $this->getManager()->packId($this->mainId);
		}
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function getMainId()
	{
		return $this->mainId;
	}

	/**
	 * @return string
	 */
	public function getCacheKey()
	{
		return $this->getManager()->getCacheKey($this->getMainId());
	}

	/**
	 * @return int
	 */
	public function getEntityType()
	{
		return $this->getManager()->getEntityType();
	}

#endregion

#region Setters
	/**
	 * @param Manager $manager
	 */
	public function setManager(Manager $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * @param array $id
	 * @return void
	 */
	public function setMainId(array $id)
	{
		$this->mainId = $id;
	}

	/**
	 * @param string $propertyName
	 * @param mixed $value
	 */
	protected function setProperty($propertyName, $value)
	{
		if (!isset($this->initialValues[$propertyName])) {
			$this->initialValues[$propertyName] = $value;
		} else {
			$this->unsavedChanges[$propertyName] = $value;
		}
	}

	/**
	 * @return bool
	 */
	public function save()
	{
		if (!count($this->unsavedChanges)) {
			return true;
		}

		$updateDatas = ':' . implode(' = ?, :', array_keys($this->unsavedChanges)) . ' = ?';
		$updateValues = array_values($this->unsavedChanges);

		$query = new Kernel\Db\Query('UPDATE @ SET ' . $updateDatas . ' WHERE ' . $this->getSaveWhere(
			), $updateValues, $this->getManager());
		$handler = $this->getApp()->getDatabase()->getHandler('sys');
		$handler->sendQuery($query);

		return true;
	}

	/**
	 * @return string
	 */
	abstract protected function getSaveWhere();

#endregion


	public function discard()
	{
		$cache_manager = $this->getApp()->getEntityCache();
		if ($cache_manager) {
			$cache_manager->delete($this, $this->getManager()->getDefaultScope());
			$cache_manager->set($this, $this->getManager()->getDefaultScope());
		}
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return get_called_class() . '::' . $this->getId();
	}

	/**
	 * @return string
	 */
	public function jsonSerialize()
	{
		return (string)$this;
	}
}