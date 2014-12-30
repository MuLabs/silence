<?php
namespace Mu\Kernel\Config;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	protected $datas;

	/**
	 * @param array $datas
	 */
	public function setAll(array $datas)
	{
		$this->datas = $datas;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		$this->datas[$key] = $value;
	}

	/**
	 * @param $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$key = explode('.', $key, 2);

		$subArray = $this->datas;
		foreach ($key as $subKey) {
			if (!isset($subArray[$subKey])) {
				return $default;
			}
			$subArray = $subArray[$subKey];
		}

		return $subArray;
	}

	/**
	 * @return array
	 */
	public function getAll()
	{
		return $this->datas;
	}

	/**
	 * @param string $file
	 */
	public function loadIniFile($file)
	{
        $path = APP_CONFIGURATION_PATH . '/' . $this->getApp()->getEnvironment();
        $filePath =  $path . '/' . $file;
		if (!file_exists($filePath)) {
			$dir = dirname($filePath);
			@mkdir($dir, 0755, true);
			touch($filePath);

			return;
		}

		$data = parse_ini_file($filePath, true, INI_SCANNER_RAW);
		$this->datas = $this->mergeDatas($this->datas, $data);
	}

	/**
	 * @param array $datas1
	 * @param array $datas2
	 * @return mixed
	 */
	protected function mergeDatas($datas1, $datas2)
	{
		foreach ($datas2 as $key => $value) {
			if (isset($datas1[$key]) && is_array($datas1[$key])) {
				$datas1[$key] = $this->mergeDatas($datas1[$key], $value);
				continue;
			}
			$datas1[$key] = $value;
		}

		return $datas1;
	}
}