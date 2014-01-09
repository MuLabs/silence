<?php
namespace Mu\Kernel\Service;

use Mu\Kernel;

class Servicer extends Kernel\Core
{
	private $services = array();
	/** @var Core[] */
	private $servicesInstance = array();
	private $servicesParameter= array();
	private $defaultParameters= array();

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

		if ($service instanceof Core) {
			$this->set($name, $service);
		} elseif (is_string($service)) {
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
		$this->servicesInstance[$name] = $service;
	}
}