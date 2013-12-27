<?php
namespace Beable\Kernel\Model;

use Beable\Kernel;

abstract class Entity extends Kernel\Core implements \JsonSerializable
{
	protected $manager;
	protected $unsavedChanges = array();
	protected $initialValues = array();
	protected $id;
	protected $isInitialized = false;

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
		$this->setId($id);

		$this->initialize();
		$this->isInitialized = true;
	}

	abstract protected function initialize();

#endregion Initialization

	public function isValid()
	{
		$id = $this->getId();
		return !empty($id);
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
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getCacheKey()
	{
		return $this->getManager()->getCacheKey($this->getId());
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
	 * @param int $id
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @param string $propertyName
	 * @param mixed $value
	 */
	protected function setProperty($propertyName, $value)
	{
		if (!$this->isInitialized) {
			$this->initialValues[$propertyName] = $value;
		} else {
			if (is_array($value)) {
				$value = json_encode($value);
			}
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
		$manager = $this->getManager();

		$updateDatas = ':' . implode(' = ?, :', array_keys($this->unsavedChanges)) . ' = ?';
		$updateValues = array_values($this->unsavedChanges);
		$updateValues[] = $this->getId();

		$sql = 'UPDATE @ SET ' . $updateDatas . ' WHERE ' . $manager->getSpecificWhere();
		$query = new Kernel\Db\Query($sql, $updateValues, $manager);
		$handler = $this->getApp()->getDatabase()->getHandler('sys');
		$handler->sendQuery($query);

		return true;
	}

	/**
	 * @return bool
	 */
	public function delete()
	{
		$manager = $this->getManager();
		if (!$manager->getProperty($manager->getDefaultGroup(), 'delete')) {
			return false;
		}

		$sql = 'UPDATE @ SET :delete = ? WHERE ' . $manager->getSpecificWhere();
		$query = new Kernel\Db\Query($sql, array(1, $this->getId()), $manager);
		$handler = $this->getApp()->getDatabase()->getHandler('sys');
		$handler->sendQuery($query);

		return true;
	}

	/**
	 * @return bool
	 */
	public function remove()
	{
		$manager = $this->getManager();
		$sql = 'DELETE FROM @ WHERE ' . $manager->getSpecificWhere();
		$query = new Kernel\Db\Query($sql, array($this->getId()), $manager);
		$handler = $this->getApp()->getDatabase()->getHandler('sys');
		$handler->sendQuery($query);

		return true;
	}

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