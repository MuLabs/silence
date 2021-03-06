<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

class Route extends Kernel\Core
{
	protected $parameters = array();
	protected $controllerName;
	protected $bundleName;
	protected $defaultVars = array();
	protected $defaultFormat;
	protected $pattern;
	protected $name;
	protected $alias;

	protected $allowedFormats = array(self::FORMAT_HTML, self::FORMAT_JSON);
	const FORMAT_HTML = 'html';
	const FORMAT_JSON = 'json';

	/**
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * @param string $controllerName
	 */
	public function setControllerName($controllerName)
	{
		$this->controllerName = $controllerName;
	}

	/**
	 * @return string
	 */
	public function getBundleName()
	{
		return $this->bundleName;
	}

	/**
	 * @param string $bundleName
	 */
	public function setBundleName($bundleName)
	{
		$this->bundleName = $bundleName;
	}

	/**
	 * @param string $pattern
	 */
	public function setPattern($pattern)
	{
		$this->pattern = $pattern;
	}

	/**
	 * @return string
	 */
	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	private function getRegexPattern()
	{
		$pattern = str_replace('/', '\/', '^' . $this->getPattern() . '(?:\?|$)(?:.*$)?');
		$pattern = preg_replace('#\{([^\}\/]+)}#iu', '(?<$1>[^\/]+)', $pattern);
		foreach ($this->getDefaultVars() as $key => $value) {
			$pattern = preg_replace('#(\(\?\<' . $key . '\>\[\^\\\/\\\?\]\+\))(\\\/)#iu', '$1?$2?', $pattern);
		}
		return $pattern;
	}

	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @param array $parameters
	 */
	private function setParameters(array $parameters)
	{
		$this->parameters = array_merge($this->getDefaultVars(), $parameters);
	}

	/**
	 * @return array
	 */
	public function getDefaultVars()
	{
		return $this->defaultVars;
	}

	/**
	 * @param array $defaultVars
	 */
	public function setDefaultVars(array $defaultVars)
	{
		$this->defaultVars = $defaultVars;
		$this->setParameters($this->getParameters()); // Update current parameters list with default values
	}

	/**
	 * @param string $format
	 */
	public function setDefaultFormat($format = self::FORMAT_HTML)
	{
		if (in_array($format, $this->allowedFormats)) {
			$this->defaultFormat = $format;
		} else {
			$this->defaultFormat = self::FORMAT_HTML;
		}
	}

	/**
	 * @return string
	 */
	public function getDefaultFormat()
	{
		return $this->defaultFormat;
	}

	/**
	 * @return array
	 */
	public function getAllowedFormats()
	{
		return $this->allowedFormats;
	}

	/**
	 * @param Kernel\Http\Request $request
	 * @return bool
	 */
	public function check(Kernel\Http\Request $request)
	{
		$routeName = $request->getParameters('rn', Kernel\Http\Request::PARAM_TYPE_GET);
		if ($routeName) {
			if ($routeName == $this->getName()) {
				$this->setParameters($request->getAllParameters(Kernel\Http\Request::PARAM_TYPE_GET));
				return true;
			}
			return false;
		}

		$params = array();

		if (!$this->getPattern()) {
			return false;
		}

        if ((bool)preg_match('#' . $this->getRegexPattern() . '#', $request->getRequestUri(), $params)) {
            $this->setParameters($params);
			return true;
		}
		return false;
	}

	/**
	 * @param array $parameters
	 * @throws Exception
	 * @return string
	 */
	public function getUrl(array $parameters = array())
	{
        $app = $this->getApp();
        foreach ($app->getRouteManager()->getTransferedParameters() as $varName => $unused) {
            $varValue = $app->getHttp()->getRequest()->getParameters(
            $varName,
                Kernel\Http\Request::PARAM_TYPE_REQUEST,
                null
            );
            if (!isset($parameters[$varName]) && $varValue !== null) {
                $parameters[$varName] = $varValue;
            }
        }

        $paramString = array();
		$pattern = $this->getPattern();
		$dash = '';

		if (isset($parameters['#'])) {
			$dash = '#' . $parameters['#'];
			unset($parameters['#']);
		}

		foreach ($parameters as $key => $value) {
			if ($value == '') {
				continue;
			}

			if (!is_array($value)) {
				$count = 0;
				$pattern = str_replace('{' . $key . '}', $value, $pattern, $count);

				if (!$count) {
					$paramString[] .= $key . '=' . $value;
				}
			} else {
				foreach ($value as $valueKey => $oneValue) {
					$paramString[] .= $key . '[' . $valueKey . ']=' . $oneValue;
				}
			}
		}

		$defaultVars = $this->getDefaultVars();
		foreach ($defaultVars as $key => $value) {
			$pattern = str_replace('{' . $key . '}', $value, $pattern);
		}

		if (preg_match_all('#{([^\/\}]+)}#ui', $pattern, $matches)) {
			if (count($matches) > 1) {
				throw new Exception(reset($matches), Exception::MISSING_PARAMETER);
			}
		}

		if (isset($pattern{0}) && $pattern{0} != '/') {
			$pattern = '/' . $pattern;
		}

		if (count($paramString)) {
			$paramString = '?' . implode('&', $paramString);
		} else {
			$paramString = '';
		}

        $localization = $app->getLocalizationService();
        if ($localization && $localization->isUrlLocaleEnabled()) {
			$pattern = '/' . $localization->getCurrentLanguage() . $pattern;
		}

		while (strpos($pattern, '//') !== false) {
			$pattern = str_replace('//', '/', $pattern);
		}

        return $app->getUrl() . $pattern . $paramString . $dash;
    }

	/**
	 * @return string
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * @param string $alias
	 */
	public function setAlias($alias)
	{
		if (is_string($alias)) {
			$this->alias = $alias;
		}
	}

	/**
	 * Is this route is an alias
	 * @return bool
	 */
	public function isAlias()
	{
		return (isset($this->alias));
	}
}