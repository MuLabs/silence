<?php
namespace Mu\Kernel\View;

use Mu\Kernel;

abstract class View extends Kernel\Core
{
    protected $template;
    protected $fragment;
	protected $service;
	protected $compileDir;
	protected $safeVars = array();
	protected $unsafeVars = array();
	protected $extension;

    /**
     * @return string
     */
    abstract public function render();

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
	public function getMainDir()
	{
		$dirList = $this->getService()->getDir();
		return reset($dirList);
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
		$this->setVar('app', $service->getApp(), false);
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
	 * @param bool $isSafe
	 */
	public function setVar($var, $value, $isSafe = true)
	{
		$app = null;
		if ($isSafe) {
			$this->safeVars[$var] = $value;
		} else {
			$this->unsafeVars[$var] = $value;
		}
	}

	/**
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	public function getVar($var, $default = null)
	{
		if (isset($this->safeVars[$var])) {
			return $this->safeVars[$var];
		} elseif (isset($this->unsafeVars[$var])) {
			return $this->unsafeVars[$var];
		} else {
			return $default;
		}
	}

	/**
	 * @return array
	 */
	public function getVars()
	{
		return array_merge($this->safeVars, $this->unsafeVars);
	}

	/**
	 * @return array
	 */
	public function getSafeVars() {
		return $this->safeVars;
	}

	/**
	 * @return array
	 */
	public function getUnsafeVars() {

		return $this->unsafeVars;
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
     * @param null $fragment
     * @return View
     */
    public function fetch($target = '', $fragment = null)
    {
        $this->template = $target;
        $this->fragment = $fragment;

        return $this;
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
			return $this->getMainDir() . '/' . $this->getService()->getSpecificDir(
			) . '/' . $target . '.' . $this->extension;
		}
		return $this->getMainDir() . '/' . $this->getService()->getSpecificDir(
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