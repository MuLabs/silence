<?php
namespace Mu\Kernel\Service;

use Mu\Kernel;

class Servicer extends Kernel\Core
{
	private $services = array();
	/** @var Core[] */
	private $servicesInstance = array();
	private $servicesParameter = array();
	private $defaultParameters = array();

	/**
	 * @param $name
	 * @param Core|string $service
	 * @param array $parameters
	 */
	public function register($name, $service, array $parameters = array())
	{
		// Manage parameters:
		if (!isset($this->servicesParameter[$name])) {
			$this->servicesParameter[$name] = array_merge($parameters, $this->defaultParameters);
		}

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
		// First get service if not an object:
		if (!is_object($service)) {
			$service = new $this->services[$name]();
		}

		// Test service type:
		$parameters = $this->servicesParameter[$name];
		if (isset($parameters['type']) && !is_a($service, $parameters['type'])) {
			throw new Exception($parameters['type'], Exception::PARAMETER_TYPE_ERROR);
		}

		// Set application and register it:
		$service->setApp($this->getApp());
		$service->initialize();
		$this->servicesInstance[$name] = $service;
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
			} catch (Exception $e) {
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