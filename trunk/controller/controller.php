<?php
namespace Beable\Kernel\Controller;

use Beable\Kernel;

abstract class Controller extends Kernel\Core
{
	protected $hasCache = false;
	protected $cacheTtl = 60;
	private $fragmentName = null;
	private $isFragmentExtracted = false;

	/************************************************************************************
	 **  GETTERS / SETTERS                                                             **
	 ************************************************************************************/

	/**
	 * @return bool
	 */
	public function hasCache()
	{
		return $this->hasCache;
	}

	/**
	 * @return int
	 */
	public function getCacheTtl()
	{
		return $this->cacheTtl;
	}

	/**
	 * @return string
	 */
	public function getCacheKey()
	{
		return '';
	}

	/**
	 * @return string|null
	 */
	public function getFragmentName()
	{
		if (!$this->isFragmentExtracted) {
			$this->fragmentName = $this->get(Kernel\Route\Service::FRAGMENT_PARAM);
			$this->isFragmentExtracted = true;
		}
		return $this->fragmentName;
	}

	/************************************************************************************
	 **  ACTION                                                                        **
	 ************************************************************************************/

	/**
	 * @return string
	 */
	abstract public function fetch();

	/**
	 * Initialization function, can be empty
	 */
	public function initialize() {

	}

	/**
	 * @throws Exception
	 * @return string
	 */
	public function fetchFragment()
	{
		$fragmentName = $this->getFragmentName();

		if (!is_string($fragmentName)) {
			return false;
		}

		if (!is_callable(array($this, $fragmentName . 'Fragment'))) {
			throw new Exception($fragmentName, Exception::INVALID_FRAGMENT_NAME);
		}

		return call_user_func(array($this, $fragmentName . 'Fragment'));
	}

	public function error302()
	{
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(302);
		$response->send();
	}

	public function error401()
	{
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(401);
		$response->send();
	}

	/**
	 * @param string $message
	 */
	public function error404($message = '')
	{
		if (!$this->getApp()->isProduction()) {
			echo 'ERROR 404 : ' . $message;
			exit;
		}
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(404);
		$response->send();
	}

	/************************************************************************************
	 **  SHORTCUTS                                                                     **
	 ************************************************************************************/

	/**
	 * @return \Beable\Kernel\View\View
	 */
	protected function getView()
	{
		return $this->getApp()->getViewManager()->getView();
	}

	/**
	 * @param string $routeName
	 * @param array $parameters
	 * @return string
	 */
	protected function getUrl($routeName, array $parameters = array())
	{
		return $this->getApp()->getRouteManager()->getUrl($routeName, $parameters);
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
	 * @param string $label
	 * @param mixed $value
	 * @return void
	 */
	public function setGet($label, $value)
	{
		$this->getApp()->getHttp()->getRequest()->setParameter($label, Kernel\Http\Request::PARAM_TYPE_GET, $value);
	}

	/**
	 * @param string $label
	 * @param mixed $value
	 * @return void
	 */
	public function setPost($label, $value)
	{
		$this->getApp()->getHttp()->getRequest()->setParameter($label, Kernel\Http\Request::PARAM_TYPE_POST, $value);
	}

	public function flushPost()
	{
		$this->getApp()->getHttp()->getRequest()->flushParameters(Kernel\Http\Request::PARAM_TYPE_POST);
	}

	public function flushGet()
	{
		$this->getApp()->getHttp()->getRequest()->flushParameters(Kernel\Http\Request::PARAM_TYPE_GET);
	}
}