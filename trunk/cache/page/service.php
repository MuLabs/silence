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
	public function exists($key, $cache_ttl)
	{
		return $this->getHandler()->exists($this->getRealKey($key), $cache_ttl);
	}

	/**
	 * @param string $key
	 * @param int $cache_ttl
	 * @return mixed
	 */
	public function get($key, $cache_ttl = 0)
	{
		return $this->getHandler()->get($this->getRealKey($key), $cache_ttl);
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value)
	{
		$this->getHandler()->set($this->getRealKey($key), $value);
	}

	public function flush()
	{
		$this->getHandler()->flush();
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
