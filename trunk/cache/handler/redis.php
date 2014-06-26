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
            // Don't throw error if connection failed
            $this->handler = null;
            return;
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
        if (!$this->handler) {
            return null;
        }
        return $this->handler->exists($key);

    }

	/**
	 * @param string $key
	 * @return mixed
	 * @throws Kernel\Cache\Exception
	 */
	public function get($key)
	{
        if (!$this->handler) {
            return null;
        }
        return unserialize($this->handler->get($key));
	}

	/**
	 * @param string $pattern
	 * @return array
	 */
	public function getKeys($pattern)
	{
        if (!$this->handler) {
            return null;
        }
        return $this->handler->getKeys($pattern);
	}

	/**
	 * @param array $keys
	 * @return mixed
	 * @throws Kernel\Cache\Exception
	 */
	public function multiGet(array $keys)
	{
        if (!$this->handler) {
            return array();
        }
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
        if (!$this->handler) {
            return null;
        }
        return $this->handler->set($key, serialize($value), $cacheTtl);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key)
	{
        if (!$this->handler) {
            return null;
        }
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
