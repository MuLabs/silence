<?php
namespace Beable\Kernel\Asset;

use Beable\Kernel;

class Service extends Kernel\Service\Core
{
	const ASSET_DIR = 'assets';

	private $forceRegenerate = false;
	private $allowedExtension = array();
	private $generator = array();
	private $vars = array();

	/**
	 * @param array $fileList
	 * @return string
	 */
	public function getAsset(array $fileList)
	{
		$asset = new Asset($this, $fileList);

		if (!$asset->exists()) {
			$asset->generate();
		}

		return $asset->getHtml();
	}

	/**
	 * @param string $ext
	 * @param string $generator
	 */
	public function registerExtension($ext, $generator)
	{
		$this->allowedExtension[$ext] = $generator;
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function registerVar($name, $value) {
		$this->vars[$name] = $value;
	}

	/**
	 * @return array
	 */
	public function getVars() {
		return $this->vars;
	}

	/**
	 * @param string $ext
	 * @return bool
	 */
	public function isExtensionAllowed($ext)
	{
		return isset($this->allowedExtension[$ext]);
	}

	/**
	 * @param Asset $asset
	 * @return Generator
	 */
	public function getGenerator(Asset $asset)
	{
		if ($this->forceRegenerate || !isset($this->generator[$asset->getKey()])) {
			$className = $this->allowedExtension[$asset->getExt()];
			/** @var Generator $generator */
			$generator = new $className($asset);
			$generator->setApp($this->getApp());
			$this->generator[$asset->getKey()] = $generator;
		}
		return $this->generator[$asset->getKey()];
	}

	public function flush()
	{
		$this->getApp()->getToolbox()->recursiveRmdir(APP_STATIC_PATH . '/assets');
	}

	public function setForceRegenerate($force = false)
	{
		$this->forceRegenerate = $force;
	}
}