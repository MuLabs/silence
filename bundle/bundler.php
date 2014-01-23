<?php
namespace Mu\Kernel\Bundle;

use Mu\Kernel;

class Bundler extends Kernel\Core
{
	/** @var Core[] */
	private $bundlesInstance = array();

	/**
	 * @param $name
	 * @param Core $bundle
	 */
	public function register($name, Core $bundle)
	{
		$bundle->setApp($this->getApp());
		$bundle->initialize();
		$this->bundlesInstance[strtolower($name)] = $bundle;
	}

	/**
	 * @param $name
	 * @throws Exception
	 * @return Core
	 */
	public function get($name)
	{
		$name = strtolower($name);
		if (!isset($this->bundlesInstance[$name])) {
			throw new Exception($name, Exception::NOT_FOUND);
		}

		return $this->bundlesInstance[$name];
	}

	/**
	 * @return Core[]
	 */
	public function getAll()
	{
		return $this->bundlesInstance;
	}
}