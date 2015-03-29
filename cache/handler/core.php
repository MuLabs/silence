<?php
namespace Mu\Kernel\Cache\Handler;

use Mu\Kernel;

abstract class Core extends Kernel\Core
{
	const SCOPE_REQUEST = 1;
	const SCOPE_LOCAL = 2;
	const SCOPE_GLOBAL = 3;
	const SCOPE_ALL = 6;

	private $host;
	private $port;

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @return int
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * @param int $port
	 */
	public function setPort($port) {
		$this->port = (int)$port;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public abstract function exists($key);

	/**
	 * @param string $key
	 * @param bool $serialize
	 * @return mixed
	 */
	public abstract function get($key, $serialize = true);

	/**
	 * @param string $pattern
	 * @return array
	 */
	public abstract function getKeys($pattern);

	/**
	 * @param array $keys
	 * @return mixed
	 */
	public abstract function multiGet(array $keys);

	/**
	 * @param string $key
	 * @param string $value
	 * @param int $cache_ttl
	 * @param bool $serialize
	 * @return mixed
	 */
	public abstract function set($key, $value, $cache_ttl = 0, $serialize = true);

	/**
	 * @param $key
	 * @return bool
	 */
	public abstract function delete($key);

	public abstract function flush();

	/**
	 * @return int
	 */
	public abstract function getScope();
}
