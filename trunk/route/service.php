<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	private $routes = array();
	private $currentRoute;

	const ROUTE_RULE_FILE = 'route.php';
	const FRAGMENT_PARAM = '__fg';

	/**
	 * @param Route $route
	 */
	private function setCurrentRoute(Route $route)
	{
		$this->currentRoute = $route;
	}

	/**
	 * @return Route
	 */
	public function getCurrentRoute()
	{
		return $this->currentRoute;
	}

	/**
	 * @return Route
	 */
	public function selectRoute()
	{
		$this->loadRoutes();
		$httpRequest = $this->getApp()->getHttp()->getRequest();

		foreach ($this->getRoutes() as $route) {
			if ($route->check($httpRequest)) {
				$this->setCurrentRoute($route);
				$params = $route->getParameters();

				foreach ($params as $key => $value) {
					if (!is_int($key)) {
						$httpRequest->setParameter($key, Kernel\Http\Request::PARAM_TYPE_GET, $value);
					}
				}

				$parameters = $httpRequest->getAllParameters(Kernel\Http\Request::PARAM_TYPE_GET);
				if ($httpRequest->getMethod() == Kernel\Http\Request::METHOD_GET && !isset($parameters[self::FRAGMENT_PARAM])) {
					unset($parameters['rn']);
					$url = $this->getUrl($route->getName(), $parameters);
					$url = substr($url, 0, strpos($url, '?'));
					$currentUrl = substr($httpRequest->getRequestUri(), 0, strpos($httpRequest->getRequestUri(), '?'));

					if (str_replace($this->getApp()->getUrl(), '', $url) !== $currentUrl) {
						$this->getApp()->redirect($route->getName(), $parameters, true);
					}
				}
				return $route;
			}
		}

		return $this->getApp()->getFactory()->getRoute();
	}

	public function loadRoutes()
	{
		$routes = $this->getRoutes();
		if (!empty($routes)) {
			return;
		}
		$filepath = APP_PATH . '/' . self::ROUTE_RULE_FILE;
		if (!file_exists($filepath)) {
			throw new Exception($filepath, Exception::FILE_NOT_FOUND);
		}

		$routesConfig = require($filepath);
		foreach ($routesConfig as $name => $routeConfig) {
			// Replace route by its alias if needed:
			$aliasName = null;
			if (isset($routeConfig['alias']) && isset($routesConfig[$routeConfig['alias']])) {
				$aliasName   = $routeConfig['alias'];
				$aliasConfig = $routesConfig[$aliasName];

				// Test if a pattern has been set, else complete it:
				if (!isset($routeConfig['pattern']) && isset($aliasConfig['pattern'])) {
					$routeConfig['pattern'] = preg_replace("#^$aliasName#", $name, $aliasConfig['pattern'], 1);
				}

				// Merge both configurations:
				$routeConfig = array_merge($aliasConfig, $routeConfig);
			}

			// Test if the route is correctly configurated:
			if (!isset($routeConfig['pattern']) || !isset($routeConfig['controller']) || !count($name)) {
				continue;
			}

			// Initialize route object:
			$default = isset($routeConfig['default']) ? $routeConfig['default'] : array();
			$route = $this->getApp()->getFactory()->getRoute($routeConfig['controller']);
			$route->setPattern($routeConfig['pattern']);
			$route->setDefaultVars($default);
			$route->setName($name);
			$route->setAlias($aliasName);

			if (isset($routeConfig['bundle'])) {
				$route->setBundleName($routeConfig['bundle']);
			}
			$this->registerRoute($route);
		}
	}

	/**
	 * @param Route $route
	 */
	public function registerRoute(Route $route)
	{
		$this->routes[$route->getName()] = $route;
	}

	/**
	 * @param string $routeName
	 * @param array $parameters
	 * @return string
	 * @throws Exception
	 */
	public function getUrl($routeName, array $parameters = array())
	{
		$routes = $this->getRoutes();
		if (isset($routes[$routeName])) {
			return $routes[$routeName]->getUrl($parameters);
		}

		throw new Exception($routeName, Exception::NOT_FOUND);
	}

	/**
	 * @param string $routeName
	 * @param string $fragmentName
	 * @param array $parameters
	 * @return string
	 * @throws Exception
	 */
	public function getFragmentUrl($routeName, $fragmentName, array $parameters = array())
	{
		$parameters[self::FRAGMENT_PARAM] = $fragmentName;
		return $this->getUrl($routeName, $parameters);
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function getCurrentRouteUrl(array $parameters = array())
	{
		return $this->getCurrentRoute()->getUrl($parameters);
	}

	/**
	 * @return Route[]
	 */
	private function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * @param string $type
	 */
	public function dumpRoutes($type = 'Apache')
	{
		$this->loadRoutes();
		$dumperName = '\\Mu\\Kernel\\Route\\Dumper\\' . $type;
		/** @var Dumper $dumper */
		$dumper = new $dumperName();
		$dumper->setApp($this->getApp());
		$dumper->dumpRoutes($this->getRoutes());
	}
}