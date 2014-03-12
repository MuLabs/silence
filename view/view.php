<?php
namespace Mu\Kernel\View;

use Mu\Kernel;

abstract class View extends Kernel\Core
{
	protected $service;
	protected $compileDir;
	protected $vars = array();
	protected $extension;

	public function __construct()
	{

	}

	/**
	 * @return string
	 */
	public function getDir()
	{
		return $this->getService()->getDir();
	}

	/**
	 * @return string
	 */
	public function getCompileDir()
	{
		return $this->getService()->getCompileDir();
	}

	/**
	 * @param Service $service
	 */
	public function setService(Service $service)
	{
		$this->service = $service;
	}

	/**
	 * @return Service
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @param string $var
	 * @param mixed $value
	 */
	public function setVar($var, $value)
	{
		$this->vars[$var] = $value;
	}

	/**
	 * @param array $vars
	 */
	public function setVars(array $vars)
	{
		$this->vars = array_merge($this->vars, $vars);
	}

	/**
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	public function getVar($var, $default = null)
	{
		if (isset($this->vars[$var])) {
			return $this->vars[$var];
		} else {
			return $default;
		}
	}

	/**
	 * @return array
	 */
	public function getVars()
	{
		return $this->vars;
	}

	/**
	 * @param string $label
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function get($label, $default = null)
	{
		return $this->getApp()->getHttp()->getRequest()->getParameters(
			$label,
			Kernel\Http\Request::PARAM_TYPE_GET,
			$default
		);
	}

	/**
	 * @param string $label
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function post($label, $default = null)
	{
		return $this->getApp()->getHttp()->getRequest()->getParameters(
			$label,
			Kernel\Http\Request::PARAM_TYPE_POST,
			$default
		);
	}

	/**
	 * @param string $label
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function request($label, $default = null)
	{
		return $this->getApp()->getHttp()->getRequest()->getParameters(
			$label,
			Kernel\Http\Request::PARAM_TYPE_REQUEST,
			$default
		);
	}


	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	abstract public function fetch($target, $fragment = null);

	/**
	 * @param string $target
	 * @throws Exception
	 */
	protected function isValid($target)
	{
		if (!file_exists($this->getDir())) {
			throw new Exception($this->getDir(), Exception::DIR_NOT_FOUND);
		}

		if ($this->compileDir && !file_exists($this->getCompileDir())) {
			throw new Exception($this->getCompileDir(), Exception::COMPILE_DIR_NOT_FOUND);
		}

		if (!file_exists($this->getTemplateFilepath($target))) {
			throw new Exception($this->getTemplateFilepath($target), Exception::TARGET_NOT_FOUND);
		}
	}

	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	public function getTemplateFilepath($target, $fragment = null)
	{
		$language = '';
		$localization = $this->getApp()->getLocalizationService();
		if ($localization && $localization->isUrlLocaleEnabled()) {
			$language = $this->getApp()->getLocalizationService()->getCurrentLanguage() . '/';
		}

		$siteName = '';
		$site = $this->getApp()->getSiteService();
		if ($site && $site->getCurrentSiteName()) {
			$siteName = $site->getCurrentSiteName() . '/';
		}

		if ($fragment === null) {
			return $this->getDir() . '/' . $target . '.' . $this->extension;
		}
		return $this->getDir(
		) . '/fragment/' . $target . '/' . $siteName . $language . $fragment . '.' . $this->extension;
	}

	/**
	 * @param string $target
	 * @param null $fragment
	 * @return string
	 */
	public function getCacheFilepath($target, $fragment = null)
	{
		return '';
	}

	/**
	 * @param string $name
	 * @return \Twig_extension
	 */
	public function getExtension($name)
	{
		return $this->getService()->getExtension($name);
	}
}