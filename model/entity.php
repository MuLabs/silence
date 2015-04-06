<?php
namespace Mu\Kernel\Model;

use Mu\Kernel;

abstract class Entity extends Kernel\Core implements \JsonSerializable, Kernel\Model\Interfaces\Entity
{
	protected $manager;
	protected $unsavedChanges = array();
	protected $initialValues = array();
	protected $id;
	protected $isInitialized = false;

	// Variable for functions internal cache
	protected $_cache = array();

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
	}

	public function initialize()
	{
		$this->setInitialized();
	}

#endregion Initialization
	/**
	 * @return bool
	 */
	public function isInitialized()
	{
		return $this->isInitialized;
	}

	protected function setInitialized()
	{
		$this->isInitialized = true;
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
		$manager = $this->getManager();
		return $manager->getCacheKey($this->getId());
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
	 * @param Kernel\Model\Interfaces\Manager $manager
	 */
	public function setManager(Kernel\Model\Interfaces\Manager $manager = null)
	{
		$this->manager = $manager;

		if ($manager !== null) {
			$this->setApp($manager->getApp());
		}
	}

	/**
	 * @param int $id
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = (int)$id;
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
            // Only register modification if something changed
            if ($this->initialValues[$propertyName] === $value) {
                return;
            }

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
            $this->initialValues[$oneKey] = $this->unsavedChanges[$oneKey];
        }

		$sql = 'UPDATE @ SET ' . $updateDatas . ' WHERE ' . $manager->getSpecificWhere();
		$query = new Kernel\Db\Query($sql, $updateValues, $manager);
		$handler = $this->getManager()->getDbHandler();
		$handler->sendQuery($query);

		$this->discard();
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
		try {
			$manager->getProperty($manager->getDefaultGroup(), 'deleted');
		} catch (Kernel\Db\Exception $e) {
			return false;
		}

		// Avoid SQL and log add if already deleted
		$deletedValue = $this->getPropertyValue('deleted');
		if ($deletedValue !== '' && $deletedValue) {
			return true;
		}

		$sql = 'UPDATE @ SET :deleted = ? WHERE ' . $manager->getSpecificWhere();
		$value = array(1, $this->getId());
		$query = new Kernel\Db\Query($sql, $value, $manager);
		$handler = $this->getManager()->getDbHandler();
		$handler->sendQuery($query);

		$this->discard();
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

		$this->discard();
		$this->logAction(Kernel\Backoffice\ActionLogger::ACTION_DELETE, array(), array());

		return true;
	}

#endregion

	public function discard()
	{
		$this->isInitialized = false;
		$this->initialize();
		$cacheManager = $this->getApp()->getEntityCache();
		if ($cacheManager) {
			// Discard entity cache and reload
			$cacheManager->delete($this, $this->getManager()->getDefaultScope());
			$cacheManager->set($this, $this->getManager()->getDefaultScope());
		}

		$pageCacheManage = $this->getApp()->getPageCache();
		if ($pageCacheManage) {
			// Discard entity cache page
			$pageCacheManage->delete('*(' . $this->getEntityType() . ':' . $this->getId() . ')*');

			// Discard manager cache page
			$pageCacheManage->delete('*{' . $this->getEntityType() . '}*');
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
		// Don't log on installing:
		if (defined('INSTALLING')) {
			return false;
		}

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
		$func2 = array($this, 'is' . ucfirst($key));

		if (is_callable($func)) {
			return call_user_func($func);
		} elseif (is_callable($func2)) {
            return call_user_func($func2);
        }

		return '';
	}

	/**
	 * @param string $property
	 * @param string $lang
	 * @return string
	 */
	public function getLocalizedValue($property, $lang = null)
	{
		return $this->getApp()->getLocalizationService()->getLocalization($this, $property, $lang);
	}

	/**
	 * @param string $property
	 * @param string $lang
	 * @param mixed $value
	 * @return bool
	 */
	public function setLocalizedValue($property, $lang, $value)
	{
		$oldVal = $this->getLocalizedValue($property, $lang);
		$result = $this->getApp()->getLocalizationService()->setLocalization($this, $lang, $property, $value);
		if ($result && $oldVal!=$value) {
			$this->logAction(
				Kernel\Backoffice\ActionLogger::ACTION_UPDATE,
				array($property.ucfirst($lang) => $oldVal),
				array($property.ucfirst($lang) => $value)
			);
		}

		return $result;
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

	/**
	 * @param array $cache
	 */
	public function reloadInternalCache(array $cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * @return array
	 */
	public function resetInternalCache()
	{
		$cache = $this->_cache;
		$this->_cache = array();
		return $cache;

	}
}