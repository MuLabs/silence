<?php
namespace Mu\Kernel\Cache\Handler;

use Mu\Kernel;

class File extends Kernel\Cache\Handler\Core
{
	protected $cache_path = CACHE_PATH;

	/**
	 * @param string $path
	 */
	public function setCachePath($path)
	{
		$this->cache_path = $path;
	}

	/**
	 * @return string
	 */
	private function getCachePath()
	{
		return $this->cache_path;
	}

	/**
	 * @return int
	 */
	public function getScope()
	{
		return parent::SCOPE_LOCAL;
	}

	/**
	 * @param string $key
	 * @param int $cacheTtl
	 * @return bool
	 */
	public function exists($key, $cacheTtl = 0)
	{
		if ($cacheTtl) {
			if ($filemtime = @filemtime($this->getPathFromKey($key))) {
				return ($filemtime + $cacheTtl) > time();
			}
			return false;
		} else {
			return file_exists($this->getPathFromKey($key));
		}
	}

	/**
	 * @param string $key
	 * @param int $cacheTtl
	 * @return mixed
	 * @throws Kernel\Cache\Exception
	 */
	public function get($key, $cacheTtl = 0)
	{
		if (!$this->exists($key, $cacheTtl)) {
			throw new Kernel\Cache\Exception($key, Kernel\Cache\Exception::KEY_NOT_FOUND);
		}
		$content = file_get_contents($this->getPathFromKey($key));
		return unserialize($content);
	}

	/**
	 * @param array $keys
	 * @param int $cacheTtl
	 * @return mixed
	 * @throws Kernel\Cache\Exception
	 */
	public function multiGet(array $keys, $cacheTtl = 0)
	{
		$values = array();
		foreach ($keys as $k => $key) {
			try {
				$values[$k] = $this->get($key);
			} catch (Kernel\Cache\Exception $e) {
				// Nothing to do, ignore error
			}
		}

		return $values;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param int $cacheTtl
	 * @return mixed|void
	 */
	public function set($key, $value, $cacheTtl = 0)
	{
		$cacheFile = $this->getPathFromKey($key);
		$cacheDir = dirname($cacheFile);

		if (!is_dir($cacheDir)) {
			mkdir($cacheDir, 0777, true);
		}
		$value = serialize($value);
		file_put_contents($cacheFile, $value);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function delete($key)
	{
		return @unlink($this->getPathFromKey($key));
	}

	public function flush()
	{
		$this->getApp()->getToolbox()->recursiveRmdir($this->getCachePath());
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function getPathFromKey($key)
	{
		$hash = sha1($key);
		return $this->getCachePath() . '/' . substr($hash, 9, 3) . '/' . substr($hash, 3, 3) . '/' . substr(
			$hash,
			6,
			3
		) . '/' .
		substr($hash, 0, 3) . '/' . substr($hash, 12) . '.cache';
	}
}
