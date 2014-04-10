<?php
namespace Mu\Kernel\Cache\Handler;

use Mu\Kernel;

abstract class Core extends Kernel\Core
{
	const SCOPE_REQUEST = 1;
	const SCOPE_LOCAL = 2;
	const SCOPE_GLOBAL = 3;
	const SCOPE_ALL = 6;

	/**
	 * @param string $key
	 * @return bool
	 */
	public abstract function exists($key);

	/**
	 * @param string $key
	 * @return mixed
	 */
	public abstract function get($key);

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
	 * @return mixed
	 */
	public abstract function set($key, $value, $cache_ttl = 0);

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
