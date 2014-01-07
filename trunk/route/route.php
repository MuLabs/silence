<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

class Route extends Kernel\Core
{
	private $parameters = array();
	private $controllerName;
	private $defaultVars = array();
	private $pattern;
	private $name;

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
			$count = 0;
			$pattern = preg_replace('#{' . $key . '}#ui', $value, $pattern, -1, $count);

			if (!$count) {
				$paramString[] .= $key . '=' . $value;
			}
		}

		$defaultVars = $this->getDefaultVars();
		if (preg_match_all('#{([^\/\}]+)}#ui', $pattern, $matches)) {
			array_shift($matches);
			foreach ($matches as $match) {
				$match = reset($match);
				if (!isset($defaultVars[$match])) {
					throw new Exception($match, Exception::MISSING_PARAMETER);
				}

				$pattern = preg_replace('#{' . $match . '}#ui', $defaultVars[$match], $pattern);
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
}