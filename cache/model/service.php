<?php
namespace Mu\Kernel\Cache\Model;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	const KEY_PREFIX = 'ENTITY||';

	/**
	 * @var Kernel\Cache\Handler\Core[]
	 */
	protected $handlers = array();

	/**
	 * @param Kernel\Cache\Handler\Core[] $handlers
	 */
	public function setHandlers(array $handlers)
	{
		foreach ($handlers as $handler) {
			if ($handler instanceof Kernel\Cache\Handler\Core) {
				$handler->setApp($this->getApp());
				$this->handlers[$handler->getScope()] = $handler;
			}
		}
		ksort($this->handlers);
	}

	/**
	 * @param string $key
	 * @param int $scope
	 * @throws Kernel\Cache\Exception
	 * @return mixed
	 */
	public function get($key, $scope)
	{
		foreach ($this->handlers as $handler) {
			if (!($scope & $handler->getScope())) {
				continue;
			}

			try {
				$result = $handler->get($this->getRealKey($key));
				return $result;
			} catch (Kernel\Cache\Exception $e) {
			}
		}

		throw new Kernel\Cache\Exception('', Kernel\Cache\Exception::KEY_NOT_FOUND);
	}

	/**
	 * @param array $keys
	 * @param int $scope
	 * @throws Kernel\Cache\Exception
	 * @return mixed[]
	 */
	public function multiGet(array $keys, $scope)
	{
		foreach ($this->handlers as $handler) {
			if (!($scope & $handler->getScope())) {
				continue;
			}

			try {
				$result = $handler->multiGet($this->getRealKeyMulti($keys));
				return $result;
			} catch (Kernel\Cache\Exception $e) {
			}
		}

		throw new Kernel\Cache\Exception('', Kernel\Cache\Exception::KEY_NOT_FOUND);
	}

	/**
	 * @param Kernel\Model\Entity $entity
	 * @param int $scope
	 * @return bool
	 */
	public function set(Kernel\Model\Entity $entity, $scope)
	{
		$cacheKey = $entity->getCacheKey();

		/**
		 * Keep entity clean of context
		 */
		$manager = $entity->getManager();
		$entity->setManager(null);
		$entity->setApp(null);
		$cache = $entity->resetInternalCache();
		foreach ($this->handlers as $handler) {
			if (!($scope & $handler->getScope())) {
				continue;
			}

			$handler->set($this->getRealKey($cacheKey), $entity);

		}
		$entity->setManager($manager);
		$entity->reloadInternalCache($cache);

	}

	/**
	 * @param Kernel\Model\Entity $entity
	 * @param int $scope
	 */
	public function delete(Kernel\Model\Entity $entity, $scope)
	{
		foreach ($this->handlers as $handler) {
			if (!($scope & $handler->getScope())) {
				continue;
			}

			$handler->delete($this->getRealKey($entity->getCacheKey()), $entity);
		}
	}

	public function flush()
	{
		foreach ($this->handlers as $handler) {
			$handler->flush();
		}
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function getRealKey($key)
	{
		return self::KEY_PREFIX . $key;
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	private function getRealKeyMulti(array $keys)
	{
		foreach ($keys as $k => $key) {
			$keys[$k] = self::KEY_PREFIX . $key;
		}

		return $keys;
	}
}
