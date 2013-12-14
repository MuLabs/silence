<?php
namespace Beable\Kernel\Cache\Handler;

use Beable\Kernel;

class Redis extends Kernel\Cache\Handler\Core
{
	/** @var  \Redis */
	private $handler;

	public function __construct()
	{
		try {
			$this->handler = new \Redis();
		} catch (\RedisException $e) {
			throw new Kernel\Cache\Exception($e->getMessage(), Kernel\Cache\Exception::FAILED_TO_CONNECT);
		}
	}

	/**
	 * @return int
	 */
	public function getScope()
	{
		return parent::SCOPE_GLOBAL;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function exists($key)
	{
		return $this->handler->exists($key);
	}

	/**
	 * @param string $key
	 * @return mixed
	 * @throws Kernel\Cache\Exception
	 */
	public function get($key)
	{
		return $this->handler->get($key);
	}

	/**
	 * @param array $keys
	 * @return mixed
	 * @throws Kernel\Cache\Exception
	 */
	public function multiGet(array $keys)
	{
		return $this->handler->mGet($keys);
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param int $cacheTtl
	 * @return mixed|void
	 */
	public function set($key, $value, $cacheTtl = 0)
	{
		return $this->handler->set($key, $value, $cacheTtl);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key)
	{
		$this->handler->delete($key);
		return true;
	}

	/**
	 * @return bool
	 */
	public function flush()
	{
		return $this->handler->flushAll();
	}
}
