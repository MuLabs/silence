<?php
namespace Mu\Kernel\Service;

use Mu\Kernel;

class Servicer extends Kernel\Core
{
	private $services = array();
	/** @var Core[] */
	private $servicesInstance = array();

	/**
	 * @param $name
	 * @param Core|string $service
	 */
	public function register($name, $service)
	{
		if ($service instanceof Core) {
			$service->setApp($this->getApp());
			$this->servicesInstance[$name] = $service;
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
				$this->servicesInstance[$name] = new $this->services[$name]();
				$this->servicesInstance[$name]->setApp($this->getApp());
			}
		}

		return $this->servicesInstance[$name];
	}
}