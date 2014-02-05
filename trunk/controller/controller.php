<?php
namespace Mu\Kernel\Controller;

use Mu\Kernel;

abstract class Controller extends Kernel\Core
{
	const REPORT_KEY 	= 'Report';
	const MESSAGE_ERROR	= 'error';
	const MESSAGE_WARN	= 'warning';
	const MESSAGE_INFO	= 'info';
	const MESSAGE_SUCCESS='success';

	protected $hasCache = false;
	protected $cacheTtl = 60;
	/** @var $view Kernel\View\View */
	private $view;
	private $fragmentName = null;
	private $isFragmentExtracted = false;
	private $messageTypes = array(self::MESSAGE_ERROR, self::MESSAGE_INFO, self::MESSAGE_SUCCESS, self::MESSAGE_WARN);

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
	public function initialize()
	{

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

	/**
	 * Send an error via error service
	 * @param int $code
	 * @param string $message
	 */
	public function error($code = 404, $message = null)
	{
		$method = "error$code";
		$service= $this->getApp()->getErrorService();
		if ($message!==null) {
			$service->$method($message);
		} else {
			$service->$method();
		}
	}

	/**
	 * Add a message to the current view
	 * @param $message
	 * @param string $type
	 */
	public function report($message, $type = self::MESSAGE_ERROR)
	{
		// Get the current view:
		$view = $this->getView();
		$data = $view->getVar($type.self::REPORT_KEY, array());

		// Append message to the current ones:
		if (is_array($message)) {
			$data = array_merge($data, $message);
		} else {
			$data[] = $message;
		}

		// Register all messages:
		$view->setVar($type.self::REPORT_KEY, $data);
	}
	public function reportError($error)
	{
		$this->report($error, self::MESSAGE_ERROR);
	}
	public function reportInfo($info)
	{
		$this->report($info, self::MESSAGE_INFO);
	}
	public function reportSuccess($success)
	{
		$this->report($success, self::MESSAGE_SUCCESS);
	}
	public function reportWarning($warn)
	{
		$this->report($warn, self::MESSAGE_WARN);
	}

	/************************************************************************************
	 **  SHORTCUTS                                                                     **
	 ************************************************************************************/

	/**
	 * @param bool $bNew
	 * @return \Mu\Kernel\View\View
	 */
	protected function getView($bNew = false)
	{
		if (!isset($this->view) || $bNew) {
			$route  = $this->getApp()->getRoute();
			$format = $this->request('format', $route->getDefaultFormat());
			if ($format == '') {
				$format = $route->getDefaultFormat();
			}
			$this->view = ($format == $route::FORMAT_JSON)
							? $this->getApp()->getJsonView()
							: $this->getApp()->getViewManager()->getView();

			// Initialize reports:
			foreach ($this->messageTypes as $type) {
				$this->view->setVar($type.self::REPORT_KEY, []);
			}
		}

		return $this->view;
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
	 * Alias of application redirection
	 * This method allow to transmit reports if they are integers
	 * @param $routeName
	 * @param array $parameters
	 * @param bool $forceRedirection
	 * @param bool $sendData
	 * @return string
	 */
	public function redirect($routeName, array $parameters = array(), $forceRedirection = false, $sendData = true)
	{
		// Get view:
		$view = $this->getView();

		// Add reports codes to the parameters:
		// !! Only transmit integers !!
		foreach ($this->messageTypes as $type) {
			if (isset($parameters[$type])) {
				continue;
			}

			$messages = [];
			foreach ($view->getVar($type.self::REPORT_KEY, []) as $report) {
				if (is_int($report)) {
					$messages[] = $report;
				}
			}
			if (!empty($messages)) {
				$parameters[$type] = implode(',', $messages);
			}
		}

		// Return application redirection:
		return $this->getApp()->redirect($routeName, $parameters, $forceRedirection, $sendData);
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