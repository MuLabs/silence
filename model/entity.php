<?php
namespace Mu\Kernel\Model;

use Mu\Kernel;

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
	/**
	 * @return bool
	 */
	public function isInitialized()
	{
		return $this->isInitialized;
	}


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
	 * @return int
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
	 * @return array
	 */
	protected function getInitialValues()
	{
		return $this->initialValues;
	}

	/**
	 * @return array
	 */
	protected function getUnsavedChanges()
	{
		return $this->unsavedChanges;
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

		$initialValues = $this->getInitialValues();
		$keys = array_keys($this->unsavedChanges);
		$oldValues = array();
		foreach ($keys as $oneKey) {
			$oldValues[$oneKey] = $initialValues[$oneKey];
		}

		$sql = 'UPDATE @ SET ' . $updateDatas . ' WHERE ' . $manager->getSpecificWhere();
		$query = new Kernel\Db\Query($sql, $updateValues, $manager);
		$handler = $this->getManager()->getDbHandler();
		$handler->sendQuery($query);

		$this->logAction(Kernel\Backoffice\ActionLogger::ACTION_UPDATE, $oldValues, $this->unsavedChanges);
		$this->unsavedChanges = array();

		return true;
	}

	/**
	 * @return bool
	 */
	public function delete()
	{
		$manager = $this->getManager();
		if (!$manager->getProperty($manager->getDefaultGroup(), 'deleted')) {
			return false;
		}

		$sql = 'UPDATE @ SET :deleted = ? WHERE ' . $manager->getSpecificWhere();
		$query = new Kernel\Db\Query($sql, array(1, $this->getId()), $manager);
		$handler = $this->getManager()->getDbHandler();
		$handler->sendQuery($query);

		$this->logAction(Kernel\Backoffice\ActionLogger::ACTION_UPDATE, array('deleted' => 0), array('deleted' => 1));

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
		$handler = $this->getManager()->getDbHandler();
		$handler->sendQuery($query);

		$this->logAction(Kernel\Backoffice\ActionLogger::ACTION_DELETE, array(), array());

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

	/**
	 * @param int $action
	 * @param array $oldValue
	 * @param array $newValue
	 * @return bool
	 */
	public function logAction($action, $oldValue, $newValue)
	{
		$bo = $this->getApp()->getBackofficeService();
		if (!$bo) {
			return false;
		}

		$actionLogger = $bo->getActionLogger();
		if (!$actionLogger) {
			return false;
		}

		return $actionLogger->create($this, $action, $oldValue, $newValue);
	}

	/**
	 * @return array
	 */
	public function getLogsList()
	{
		$bo = $this->getApp()->getBackofficeService();
		if (!$bo) {
			return array();
		}

		$actionLogger = $bo->getActionLogger();
		if (!$actionLogger) {
			return array();
		}

		return $actionLogger->getLogsFromObject($this);
	}

	/**
	 * @param $key
	 * @return mixed|string
	 */
	public function getPropertyValue($key)
	{
		$func = array($this, 'get' . ucfirst($key));
		if (is_callable($func)) {
			return call_user_func($func);
		}

		return '';
	}

	/**
	 * @param string $property
	 * @param string $lang
	 * @return string
	 */
	public function getLocalizedValue($property, $lang = null) {
		return $this->getApp()->getLocalizationService()->getLocalization($this, $property, $lang);
	}

	/**
	 * @param string $property
	 * @param string $lang
	 * @param mixed $value
	 * @return bool
	 */
	public function setLocalizedValue($property, $lang, $value) {
		return $this->getApp()->getLocalizationService()->setLocalization($this, $lang, $property, $value);
	}

	/**
	 * @param $key
	 * @param $value
	 * @return string
	 */
	public function toStringValue($key, $value)
	{
		if (is_array($value)) {
			$value = print_r($value, true);
		}
		return '' . $value;
	}
}