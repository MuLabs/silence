<?php
namespace Mu\Kernel\Http;

use Mu\Kernel;

class Request
{
	const METHOD_GET = 1;
	const METHOD_POST = 2;
	const METHOD_PUSH = 3;
	const METHOD_HEAD = 4;

	const AUTH_TYPE_CONFIG = 1;
	const AUTH_TYPE_COOKIE = 2;
	const AUTH_TYPE_HTTP = 3;
	const AUTH_TYPE_SESSION = 4;

	const PARAM_TYPE_GET = 1;
	const PARAM_TYPE_POST = 2;
	const PARAM_TYPE_COOKIE = 3;
	const PARAM_TYPE_REQUEST = 4;
	const PARAM_TYPE_SESSION = 5;
	const PARAM_TYPE_SERVER = 6;

	protected $method;
	protected $time;
	protected $queryString;
	protected $documentRoot;
	protected $scriptFilename;
	protected $scriptName;
	protected $pathTranslated;
	protected $requestUri;
	protected $pathInfo;
	protected $origPathInfo;
    protected $accepts;
    protected $contentType;

	protected $phpAuthDigest;
	protected $phpAuthUser;
	protected $phpAuthPw;
	protected $authType;

	protected $serverAddr;
	protected $serverName;
	protected $serverSoftware;
	protected $serverProtocol;
	protected $serverAdmin;
	protected $serverPort;
	protected $serverSignature;

	protected $remoteAddr;
	protected $remoteHost;
	protected $remotePort;

	protected $requestHeader;

	/********************/
	/*     STANDARD     */
	/********************/
	public function __construct()
	{
	}

	private function __clone()
	{
	}

	public function __destruct()
	{
	}

	/********************/
	/*     SETTER       */
	/********************/
    /**
     * @param string $accept
     */
    public function setHttpAccept($accept = '')
    {
        $this->accepts = (string)$accept;
    }

    /**
     * @param string $content
     */
    public function setContentType($content = '')
    {
        $this->contentType = (string)$content;
    }

	/**
	 * @param Header\Request $header
	 */
	public function setHeader(Header\Request $header)
	{
		$this->requestHeader = $header;
	}

	/**
	 * @param string $method
	 */
	public function setMethod($method)
	{
		switch ($method) {
			default:
			case 'GET':
				$this->method = self::METHOD_GET;
				break;
			case 'POST':
				$this->method = self::METHOD_POST;
				break;
			case 'PUSH':
				$this->method = self::METHOD_PUSH;
				break;
			case 'HEAD':
				$this->method = self::METHOD_HEAD;
				break;
		}
	}

	/**
	 * @param string $uri
	 */
	public function setRequestUri($uri)
	{
		$this->requestUri = $uri;
	}

	/********************/
	/*    GETTER        */
	/********************/
    /**
     * @return string
     */
    public function getHttpAccept()
    {
        return $this->accepts;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

	/**
	 * @param string $label
	 * @param int $type
	 * @param mixed $default
	 * @return mixed|null
	 */
	public function getParameters($label, $type, $default = null)
	{
		switch ($type) {
			case self::PARAM_TYPE_GET:
				return (isset($_GET[$label])) ? $_GET[$label] : $default;
				break;

			case self::PARAM_TYPE_POST:
				return (isset($_POST[$label])) ? $_POST[$label] : $default;
				break;

			case self::PARAM_TYPE_COOKIE:
				return (isset($_COOKIE[$label])) ? $_COOKIE[$label] : $default;
				break;

			case self::PARAM_TYPE_REQUEST:
				return ((isset($_POST[$label])) ? $_POST[$label] : (isset($_GET[$label]) ? $_GET[$label] : $default));
				break;

            case self::PARAM_TYPE_SESSION:
                return (isset($_SESSION[$label])) ? $_SESSION[$label] : $default;
                break;

            case self::PARAM_TYPE_SERVER:
                return (isset($_SERVER[$label])) ? $_SERVER[$label] : $default;
                break;

			default:
				return $default;
				break;
		}
	}

	/**
	 * @param int $type
	 * @return mixed
	 */
	public function getAllParameters($type)
	{
		switch ($type) {
			case self::PARAM_TYPE_GET:
				return $_GET;
				break;

			case self::PARAM_TYPE_POST:
				return $_POST;
				break;

			case self::PARAM_TYPE_COOKIE:
				return $_COOKIE;
				break;

			case self::PARAM_TYPE_REQUEST:
				return $_REQUEST;
				break;

			case self::PARAM_TYPE_SESSION:
				return $_SESSION;
				break;

			default:
				return array();
				break;
		}
	}

	/**
	 * Test if TYPE have at least one parameter
	 * Ex: haveParameters(PARAM_TYPE_GET)
	 * @param $type
	 * @return bool|int
	 */
	public function haveParameters($type)
	{
		switch ($type) {
			case self::PARAM_TYPE_GET:
				return (count($_GET));
				break;

			case self::PARAM_TYPE_POST:
				return (count($_POST));
				break;

			case self::PARAM_TYPE_COOKIE:
				return (count($_COOKIE));
				break;

			case self::PARAM_TYPE_REQUEST:
				return (count($_REQUEST));
				break;

			case self::PARAM_TYPE_SESSION:
				return (count($_SESSION));
				break;

			default:
				return false;
				break;
		}
	}

	/**
	 * @param string $label
	 * @param int $type
	 * @param mixed $value
	 * @return void
	 */
	public function setParameter($label, $type, $value)
	{
		switch ($type) {
			case self::PARAM_TYPE_GET:
				$_GET[$label] = $value;
				$_REQUEST[$label] = $value;
				break;

			case self::PARAM_TYPE_POST:
				$_POST[$label] = $value;
				$_REQUEST[$label] = $value;
				break;

			case self::PARAM_TYPE_SESSION:
				$_SESSION[$label] = $value;
				break;
		}
	}

    /**
     * @param string $label
     * @param int $type
     * @return void
     */
    public function removeParameter($label, $type)
    {
        switch ($type) {
            case self::PARAM_TYPE_GET:
                unset($_GET[$label]);
                unset($_REQUEST[$label]);
                break;

            case self::PARAM_TYPE_POST:
                unset($_POST[$label]);
                unset($_REQUEST[$label]);
                break;

            case self::PARAM_TYPE_SESSION:
                unset($_SESSION[$label]);
                break;
        }
    }

	/**
	 * @param int $type
	 * @return void
	 */
	public function flushParameters($type)
	{
		switch ($type) {
			case self::PARAM_TYPE_GET:
				foreach ($_GET as $key => $value) {
					unset($_REQUEST[$key]);
				}
				$_GET = array();
				break;

			case self::PARAM_TYPE_POST:
				foreach ($_POST as $key => $value) {
					unset($_REQUEST[$key]);
				}
				$_POST = array();
				break;
		}
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return int
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->queryString;
	}

	/**
	 * @return string
	 */
	public function getDocumentRoot()
	{
		return $this->documentRoot;
	}

	/**
	 * @return string
	 */
	public function getScriptFilename()
	{
		return $this->scriptFilename;
	}

	/**
	 * @return string
	 */
	public function getScriptName()
	{
		return $this->scriptName;
	}

	/**
	 * @return string
	 */
	public function getPathTranslated()
	{
		return $this->pathTranslated;
	}

	/**
	 * @return string
	 */
	public function getRequestUri()
	{
		return $this->requestUri;
	}

	/**
	 * @return string
	 */
	public function getPathInfo()
	{
		return $this->pathInfo;
	}

	/**
	 * @return string
	 */
	public function getOrigPathInfo()
	{
		return $this->origPathInfo;
	}

	/**
	 * @return string
	 */
	public function getPhpAuthDigest()
	{
		return $this->phpAuthDigest;
	}

	/**
	 * @return string
	 */
	public function getPhpAuthUser()
	{
		return $this->phpAuthUser;
	}

	/**
	 * @return string
	 */
	public function getPhpAuthPw()
	{
		return $this->phpAuthPw;
	}

	/**
	 * @return int
	 */
	public function getAuthType()
	{
		return $this->authType;
	}

	/**
	 * @return string
	 */
	public function getServerAddr()
	{
		return $this->serverAddr;
	}

	/**
	 * @return string
	 */
	public function getServerName()
	{
		return $this->serverName;
	}

	/**
	 * @return string
	 */
	public function getServerSoftware()
	{
		return $this->serverSoftware;
	}

	/**
	 * @return string
	 */
	public function getServerProtocol()
	{
		return $this->serverProtocol;
	}

	/**
	 * @return string
	 */
	public function getServerAdmin()
	{
		return $this->serverAdmin;
	}

	/**
	 * @return int
	 */
	public function getServerPort()
	{
		return $this->serverPort;
	}

	/**
	 * @return string
	 */
	public function getServerSignature()
	{
		return $this->serverSignature;
	}

	/**
	 * @return string
	 */
	public function getRemoteAddr()
	{
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
	}

	/**
	 * @return string
	 */
	public function getRemoteHost()
	{
		return $this->remoteHost;
	}

	/**
	 * @return int
	 */
	public function getRemotePort()
	{
		return $this->remotePort;
	}

	/**
	 * @return Header\Request
	 */
	public function getRequestHeader()
	{
		return $this->requestHeader;
	}
}
