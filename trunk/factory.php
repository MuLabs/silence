<?php
namespace Beable\Kernel;

use Beable\Kernel;

class Factory extends Kernel\Service\Core
{
	/**
	 * @param string $classname
	 * @return Core
	 */
	public function get($classname)
	{
		/** @var $obj Core */
		$obj = new $classname();
		$obj->setApp($this->getApp());
		return $obj;
	}

	/**
	 * @param $classname
	 * @return Controller\Controller
	 */
	public function getController($classname)
	{
		return $this->get($classname);
	}

	/**
	 * @param string|null $controllerName
	 * @return Route\Route
	 */
	public function getRoute($controllerName = null)
	{
		/** @var Kernel\Route\Route $route */
		$route = $this->get('\\Beable\\Kernel\\Route\\Route');
		$route->setControllerName($controllerName);

		return $route;
	}
}