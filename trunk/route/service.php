<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	protected $routes = array();
    protected $transferedParameters = array();
    protected $currentRoute;

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
     * @param string $name
     */
    public function addTransferedParameter($name)
    {
        $this->transferedParameters[$name] = true;
    }

    /**
     * @return array
     */
    public function getTransferedParameters()
    {
        return $this->transferedParameters;
    }

    /**
	 * @return Route
	 */
	public function selectRoute()
	{
        $app = $this->getApp();
        $httpRequest = $app->getHttp()->getRequest();

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
                if ($httpRequest->getMethod(
                    ) == Kernel\Http\Request::METHOD_GET && !isset($parameters[self::FRAGMENT_PARAM])
                ) {
                    unset($parameters['rn']);
                    $url = $this->getUrl($route->getName(), $parameters);
                    $endUri = strpos($url, '?');
                    $url = substr($url, 0, $endUri ? $endUri : strlen($url));

                    $endUri = strpos($httpRequest->getRequestUri(), '?');
                    $currentUrl = substr(
                        $httpRequest->getRequestUri(),
                        0,
                        $endUri ? $endUri : strlen($httpRequest->getRequestUri())
                    );

                    $localization = $app->getLocalizationService();
                    if ($localization && $localization->isUrlLocaleEnabled()) {
                        if ($localization->isLocaleFromUrl()) {
                            $currentUrl = '/' . $localization->getCurrentLanguage() . $currentUrl;
                        }
                    }

                    if (str_replace($app->getUrl(), '', $url) !== $currentUrl) {
                        $app->redirect($route->getName(), $parameters, true);
                    }
                }
                return $route;
			}
		}

        return $app->getFactory()->getRoute();
    }

	public function loadRoutes()
	{
        $app = $this->getApp();
        $filepath = APP_PATH . '/' . self::ROUTE_RULE_FILE;
		if (!file_exists($filepath)) {
			throw new Exception($filepath, Exception::FILE_NOT_FOUND);
		}

        $siteService = $app->getSiteService();

        $this->routes = array();
        $routesConfig = require($filepath);

        foreach ($routesConfig as $name => $routeConfig) {
            // Replace route by its alias if needed:
            $aliasName = null;
            if (isset($routeConfig['alias']) && isset($routesConfig[$routeConfig['alias']])) {
                $aliasName = $routeConfig['alias'];
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

            // Throw an exception if siteService is not activated and siteIn/siteOut is used
            if ((isset($routeConfig['siteIn']) || isset($routeConfig['siteOut'])) && !$siteService) {
                throw new Exception('', Exception::MISSING_SITE_SERVICE);
            }

            $currentSite = $siteService->getCurrentSiteName();
            if (isset($currentSite) && (isset($routeConfig['siteIn']) && !in_array(
                        $currentSite,
                        explode(',', $routeConfig['siteIn'])
                    ))
                || (isset($routeConfig['siteOut']) && in_array($currentSite, explode(',', $routeConfig['siteOut'])))
            ) {
                continue;
            }

            // Initialize route object:
            $default = isset($routeConfig['default']) ? $routeConfig['default'] : array();
            $format = isset($routeConfig['format']) ? $routeConfig['format'] : '';
            $route = $app->getFactory()->getRoute($routeConfig['controller']);
            $route->setPattern($routeConfig['pattern']);
            $route->setDefaultVars($default);
            $route->setDefaultFormat($format);
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
     * @param bool $force
     * @return Route[]
     */
    public function getRoutes($force = false)
    {
        if ($force || empty($this->routes)) {
            $this->loadRoutes();
		}
		return $this->routes;
	}

	/**
	 * @param string $type
	 */
	public function dumpRoutes($type = 'Apache')
	{
		$dumperName = '\\Mu\\Kernel\\Route\\Dumper\\' . $type;
		/** @var Dumper $dumper */
		$dumper = new $dumperName();
		$dumper->setApp($this->getApp());
        $dumper->dumpSites($this->getApp()->getSiteService()->getSites());
    }
}