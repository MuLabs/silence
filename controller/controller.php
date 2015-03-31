<?php
namespace Mu\Kernel\Controller;

use Mu\Kernel;

abstract class Controller extends Kernel\Core
{
	const REPORT_KEY = 'Report';
	const MESSAGE_ERROR = 'error';
	const MESSAGE_WARN = 'warning';
	const MESSAGE_INFO = 'info';
	const MESSAGE_SUCCESS = 'success';

	protected $hasCache = false;
	protected $cacheTtl = 0;
	/** @var $view Kernel\View\View */
	protected $view;
	protected $http;
	protected $fragmentName = null;
	protected $isFragmentExtracted = false;
	protected $messageTypes = array(self::MESSAGE_ERROR, self::MESSAGE_INFO, self::MESSAGE_SUCCESS, self::MESSAGE_WARN);
    protected $messageCodes = array();

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
	 * @param bool
	 */
	public function setCache($bCache = false)
	{
		$this->hasCache = (bool)$bCache;
	}

	/**
	 * @return int
	 */
	public function getCacheTtl()
	{
		return $this->cacheTtl;
	}

	/**
	 * @return Kernel\Http\Service
	 */
	public function getHttp()
	{
		if (!isset($this->http)) {
			$this->http = $this->getApp()->getHttp();
		}

		return $this->http;
	}

	/**
	 * @return array
	 */
	public function getCacheKeyElements()
	{
		return array(
		);
	}

	protected function getFinalCacheKeyElements($replaceValues = true) {
		$cacheElements = $this->getCacheKeyElements();
		$cacheElements['internal'] = array(
			'renderer' => $this->getCurrentRenderer(),
			'info' => $this->request('info'),
			'warn' => $this->request('warn'),
			'success' => $this->request('success'),
			'error' => $this->request('error'),
		);

		$localization = $this->getApp()->getLocalizationService();
		if ($localization && $localization->getCurrentLanguage()) {
			$cacheElements['internal']['lang'] = $localization->getCurrentLanguage();
		}

		$site = $this->getApp()->getSiteService();
		if ($site && $site->getCurrentSite()) {
			$cacheElements['internal']['site'] = $site->getCurrentSite();
		}

		if (!is_array($cacheElements)) {
			return array();
		}

		$finalCacheElements = array();
		foreach ($cacheElements as $gType => $variables) {
			if (empty($variables)) {
				continue;
			}

			foreach ($variables as $key => $oneVariable) {
				if ($gType === 'entity') {
					if ($oneVariable['type']['type'] == 'get') {
						$entityType = ($replaceValues) ? $this->get($oneVariable['type']['value']) : '$'.$oneVariable['type']['value'];
					} else {
						$entityType = $oneVariable['type']['value'];
					}


					if ($oneVariable['id']['type'] == 'get') {
						$entityId = ($replaceValues) ? $this->get($oneVariable['id']['value']) : '$'.$oneVariable['id']['value'];
					} else {
						$entityId = $oneVariable['id']['value'];
					}
				} elseif ($gType === 'internal') {
					$value = $oneVariable;
				} else {
					$vType = $oneVariable['type'];
					$value = $oneVariable['value'];
					if ($vType == 'get') {
						$value = ($replaceValues) ? $this->get($value) : '$' . $value;
					}
				}

				switch ($gType) {
					case 'entity':
						$finalCacheElements[] = '(' . $entityType . ':' . $entityId . ')';
						break;
					case 'manager':
						$finalCacheElements[] = '{' . $value . '}';
						break;
					default:
					case 'internal':
					case 'other':
						if (empty($value)) {
							continue;
						}
						$finalCacheElements[] = $value;
						break;
				}
			}
		}

		return $finalCacheElements;
	}

	/**
	 * @return string
	 */
	public function getDynamicCacheKey() {
		$cacheElements = $this->getFinalCacheKeyElements(false);

		$trail = implode('|', $cacheElements);
		return get_called_class() . '|' . $trail;
	}

	/**
	 * @return string
	 */
    public function getCacheKey()
    {
		$cacheElements = $this->getFinalCacheKeyElements();

		$trail = implode('|', $cacheElements);
        return get_called_class() . '|' . $trail;
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

    /**
     * @param null $code
     * @return string
     */
    protected function getMessageFromCode($code = null)
    {
        return (!isset($this->messageCodes[$code])) ? '' : gettext($this->messageCodes[$code]);
    }

	/************************************************************************************
	 **  ACTION                                                                        **
	 ************************************************************************************/

	/**
	 * @return Kernel\View\View
	 */
	abstract public function fetch();

	/**
	 * Initialization function, can be empty
	 */
	public function initialize()
	{
        return false;
	}

	/**
	 * @throws Exception
	 * @return Kernel\View\View
	 */
	public function fetchFragment()
	{
		$fragmentName = $this->getFragmentName();
		if (!is_string($fragmentName)) {
			return false;
		}

		$return = call_user_func(array($this, $fragmentName . 'Fragment'));
		if (!$return) {
			throw new Exception($fragmentName, Exception::INVALID_FRAGMENT_NAME);
		}

		return $return;
	}

	/**
	 * Keep this class for authentication control
	 */
	public function preFetch()
	{

	}

    /**
     * @param $content
     * @return string
     */
    public function postRender($content)
    {
        return $content;
    }

    /**
	 * Send an error via error service
	 * @param int $code
	 * @param string $message
	 */
	public function error($code = 404, $message = null)
	{
		$method = "error$code";
		$service = $this->getApp()->getErrorService();
		if ($message !== null) {
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
		if (empty($message)) {
			return;
		}

		// Get the current view:
		$view = $this->getView();
		$data = $view->getVar($type . self::REPORT_KEY, array());

		// Append message to the current ones:
        // Use gettext translation
        if (is_array($message)) {
			$data = array_merge($data, $message);
		} else {
			$data[] = $message;
		}

		// Register all messages:
		$view->setVar($type . self::REPORT_KEY, $data);
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
	public function getView($bNew = false)
	{
		if (!isset($this->view) || $bNew) {
			$this->view = $this->getApp()->getViewManager()->getView();

			// Initialize reports:
			foreach ($this->messageTypes as $type) {
				$this->view->setVar($type . self::REPORT_KEY, []);

				// Try to get previous messages:
				$messages = $this->request($type, false);
				if (!$messages) {
					continue;
				}

				$messages = explode(',', $messages);
				foreach ($messages as $code) {
					$message = (is_numeric($code)) ? $this->getMessageFromCode($code) : $code;
					$this->report($message, $type);
				}
			}

			// Initialize redirect link:
            $redirect = $this->request('redirect', '');
            if ($redirect!='') {
                $this->view->setVar('redirect', $redirect);
            }
		}

		return $this->view;
	}

    /**
     * @return Kernel\Renderer\Handler
     */
    public function getRenderer()
    {
        return $this->getApp()->getRendererManager()->getHandler();
    }

    /**
     * @param string
     */
    public function setRenderer($type)
    {
        $this->getApp()->getRendererManager()->setHandler($type);
    }

    /**
     * @return string
     */
    public function getCurrentRenderer()
    {
        return $this->getApp()->getRendererManager()->getCurrentRender();
    }

    /**
     * @return bool
     */
    public function isHtmlRendering()
    {
        return ($this->getCurrentRenderer() == $this->getApp()->getRendererManager()->getConstant('HANDLER_HTML'));
    }

	/**
	 * @return bool
	 */
	public function isJsonRendering()
	{
		return ($this->getCurrentRenderer() == $this->getApp()->getRendererManager()->getConstant('HANDLER_JSON'));
	}

	/**
	 * @return bool
	 */
	public function isHtmlJsonRendering()
	{
		return ($this->getCurrentRenderer() == $this->getApp()->getRendererManager()->getConstant('HANDLER_HJSON'));
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
     * @param bool   $bEncode
     * @return string
     */
    protected function getCurrentUrl($bEncode = false)
    {
		$params = $this->getHttp()->getRequest()->getAllParameters(
            Kernel\Http\Request::PARAM_TYPE_REQUEST
        );

        // Remove redirect link:
        unset($params['redirect']);

        // Build URL:
        $url = $this->getUrl($this->getApp()->getRoute()->getName(), $params);

        // Return URL:
        return ($bEncode) ? base64_encode($url) : $url;
    }

	/**
	 * @return bool
	 */
	public function hasGet() {
		return $this->getHttp()->getRequest()->haveParameters(Kernel\Http\Request::PARAM_TYPE_GET);
	}

	/**
	 * @param string $label
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function get($label, $default = null)
	{
		return $this->getHttp()->getRequest()->getParameters(
			$label,
			Kernel\Http\Request::PARAM_TYPE_GET,
			$default
		);
	}

	/**
	 * @return bool
	 */
	public function hasPost() {
		return $this->getHttp()->getRequest()->haveParameters(Kernel\Http\Request::PARAM_TYPE_POST);
	}

	/**
	 * @param string $label
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function post($label, $default = null)
	{
		return $this->getHttp()->getRequest()->getParameters(
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
		return $this->getHttp()->getRequest()->getParameters(
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
		if (!$this->isHtmlJsonRendering()) {
			foreach ($this->messageTypes as $type) {
				if (isset($parameters[$type])) {
					continue;
				}

				$messages = array();
				foreach ($view->getVar($type . self::REPORT_KEY, []) as $report) {
					if (is_int($report)) {
						$messages[] = $report;
					}
				}
				if (!empty($messages)) {
					$parameters[$type] = implode(',', $messages);
				}
			}
		}

        // Transfer format and redirection link:
        if (!$this->isHtmlRendering()) {
			if ($this->isHtmlJsonRendering()) {
				$forceRedirection = false;
			}

            // Update redirection link or generate it:
            if (isset($parameters['redirect'])) {
                $parameters['redirect'] = $this->getCurrentUrl(true);
            } else {
                $params = $parameters;
                $parameters['redirect'] = base64_encode($this->getApp()->getRouteManager()->getUrl($routeName, $params));
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
		$this->getHttp()->getRequest()->setParameter($label, Kernel\Http\Request::PARAM_TYPE_GET, $value);
	}

	/**
	 * @param string $label
	 * @param mixed $value
	 * @return void
	 */
	public function setPost($label, $value)
	{
		$this->getHttp()->getRequest()->setParameter($label, Kernel\Http\Request::PARAM_TYPE_POST, $value);
	}

	public function flushPost()
	{
		$this->getHttp()->getRequest()->flushParameters(Kernel\Http\Request::PARAM_TYPE_POST);
	}

	public function flushGet()
	{
		$this->getHttp()->getRequest()->flushParameters(Kernel\Http\Request::PARAM_TYPE_GET);
	}
}