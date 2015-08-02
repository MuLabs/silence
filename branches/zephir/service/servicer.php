<?php
namespace Mu\Kernel\Service;

use Mu\Kernel;

class Servicer extends Kernel\Core
{
	protected $services = array();
	/** @var Core[] */
	protected $servicesInstance = array();

	/**
	 * @param $name
	 * @param Core|string $service
	 */
	public function register($name, $service)
	{
        $name = strtolower($name);

		if (isset($this->servicesInstance[$name])) {
			unset($this->servicesInstance[$name]);
		}

		if (is_string($service)) {
			$this->services[$name] = $service;
		}
	}

	/**
	 * @param $name
	 * @throws Exception
	 * @return Core
	 */
	public function get($name)
	{
        $name = strtolower($name);
		if (!isset($this->servicesInstance[$name])) {
			if (!isset($this->services[$name])) {
				throw new Exception($name, Exception::NOT_FOUND);
			} else {
				$this->set($name);
			}
		}

		return $this->servicesInstance[$name];
	}

	/**
	 * Correctly check parameters before saving service instance
	 * @param $name
	 * @param Kernel\Service\Core $service
	 * @throws Exception
	 */
	private function set($name, Kernel\Service\Core $service = null)
	{
        $name = strtolower($name);
		// First get service if not an object:
		if (!is_object($service)) {
			$service = new $this->services[$name]();
		}

		// Set application and register it:
		$this->servicesInstance[$name] = $service;
		$app = $this->getApp();
		$service->setApp($app);
		$service->initialize();
		$app->configureService($name, $service);
	}

	/**
	 * @param string $stdOut
	 * @throws \Exception
	 * @throws Exception
	 * @return bool
	 */
	public function createStructure($stdOut = '\print')
	{
		foreach ($this->services as $serviceName => $class) {
			try {
				$service = $this->get($serviceName);
				$service->createStructure($stdOut);
			} catch (Kernel\Exception $e) {
				if ($e->getCode() == Exception::NOT_FOUND) {
					continue;
				}
				throw $e;
			}
		}
	}

	/**
	 * @param string $stdOut
	 * @throws \Exception
	 * @throws Exception
	 * @return bool
	 */
	public function createDefaultDataSet($stdOut = '\print')
	{
		foreach ($this->services as $serviceName => $class) {
			try {
				$service = $this->get($serviceName);
				$service->createDefaultDataSet($stdOut);
			} catch (Exception $e) {
				if ($e->getCode() == Exception::NOT_FOUND) {
					continue;
				}
				throw $e;
			}
		}
	}
}