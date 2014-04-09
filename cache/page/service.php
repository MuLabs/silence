<?php
namespace Mu\Kernel\Cache\Page;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	const KEY_PREFIX = 'PAGE||';

	protected $handler;

	/**
	 * @param Kernel\Cache\Handler\Core $handler
	 */
	public function setHandler(Kernel\Cache\Handler\Core $handler)
	{
		if ($handler->getScope() != Kernel\Cache\Handler\Core::SCOPE_REQUEST) {
			$handler->setApp($this->getApp());
			$this->handler = $handler;
		}
	}

	/**
	 * @return Kernel\Cache\Handler\Core
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * @param string $key
	 * @param int $cache_ttl
	 * @return bool
	 */
	public function exists($key, $cache_ttl = 0)
	{
		$handler = $this->getHandler();

		if (!$handler) {
			return false;
		}
		return $handler->exists($this->getRealKey($key), $cache_ttl);
	}

	/**
	 * @param string $key
	 * @param int $cache_ttl
	 * @throws \Mu\Kernel\Cache\Exception
	 * @return mixed
	 */
	public function get($key, $cache_ttl = 0)
	{
		$handler = $this->getHandler();

		if (!$handler) {
			throw new Kernel\Cache\Exception(Kernel\Cache\Exception::KEY_NOT_FOUND);
		}
		$result = $handler->get($this->getRealKey($key), $cache_ttl);
		if (!is_string($result)) {
			throw new Kernel\Cache\Exception(Kernel\Cache\Exception::KEY_NOT_FOUND);
		}

		return $result;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value)
	{
		$handler = $this->getHandler();

		if (!$handler) {
			return;
		}
		$handler->set($this->getRealKey($key), $value);
	}

	public function flush()
	{
		$handler = $this->getHandler();

		if (!$handler) {
			return;
		}
		$handler->flush();
	}

	/**
	 * @param string $key
	 */
	public function delete($key)
	{
		$handler = $this->getHandler();

		if (!$handler) {
			return;
		}
		$handler->delete($this->getRealKey($key));
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function getRealKey($key)
	{
		return self::KEY_PREFIX . $key;
	}
}
