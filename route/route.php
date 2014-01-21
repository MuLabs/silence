<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

class Route extends Kernel\Core
{
	private $parameters = array();
	private $controllerName;
	private $bundleName;
	private $defaultVars = array();
	private $pattern;
	private $name;
	private $alias;

	private $allowedFormats = array(self::FORMAT_HTML, self::FORMAT_JSON);
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
			$pattern = preg_replace('#(\(\?\<' . $key . '\>\[\^\\\/\]\+\))(\\\/)#iu', '$1?$2?', $pattern);
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

		if (!!preg_match('#' . $this->getRegexPattern() . '#', $request->getRequestUri(), $params)) {
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
		$paramString = array();
		$pattern = $this->getPattern();
		foreach ($parameters as $key => $value) {
			if ($value == '') {
				continue;
			}
			$count = 0;
			$pattern = str_replace('{' . $key . '}', $value, $pattern, $count);

			if (!$count) {
				$paramString[] .= $key . '=' . $value;
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

		return $this->getApp()->getUrl() . str_replace('//', '/', $pattern . $paramString);
	}

	public function getAlias()
	{
		return $this->alias;
	}
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