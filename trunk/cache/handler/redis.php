<?php
namespace Mu\Kernel\Cache\Handler;

use Mu\Kernel;

class Redis extends Kernel\Cache\Handler\Core
{
	/** @var  \Redis */
	protected $handler;

	public function __construct($host = '127.0.0.1', $port = 6379)
	{
		try {
			$this->handler = new \Redis();
			$this->handler->connect($host, $port);
			$this->handler->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
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
		return unserialize($this->handler->get($key));
	}

	/**
	 * @param array $keys
	 * @return mixed
	 * @throws Kernel\Cache\Exception
	 */
	public function multiGet(array $keys)
	{
		$result = $this->handler->mGet($keys);

		if (is_array($result)) {
			foreach ($result as $key => $value) {
				$result[$key] = unserialize($value);
			}
		} else {
			$result = array();
		}

		return $result;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param int $cacheTtl
	 * @return mixed|void
	 */
	public function set($key, $value, $cacheTtl = 0)
	{
		return $this->handler->set($key, serialize($value), $cacheTtl);
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
