<?php
namespace Beable\Kernel\Http;

use Beable\Kernel;

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

	private $method;
	private $time;
	private $queryString;
	private $documentRoot;
	private $scriptFilename;
	private $scriptName;
	private $pathTranslated;
	private $requestUri;
	private $pathInfo;
	private $origPathInfo;

	private $phpAuthDigest;
	private $phpAuthUser;
	private $phpAuthPw;
	private $authType;

	private $serverAddr;
	private $serverName;
	private $serverSoftware;
	private $serverProtocol;
	private $serverAdmin;
	private $serverPort;
	private $serverSignature;

	private $remoteAddr;
	private $remoteHost;
	private $remotePort;

	private $requestHeader;

	/********************/
	/**    STANDART    **/
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
	/**     SETTER     **/
	/********************/
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
		$this->method = $method;
	}

	/**
	 * @param string $uri
	 */
	public function setRequestUri($uri)
	{
		$this->requestUri = $uri;
	}

	/********************/
	/**     GETTER     **/
	/********************/
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
				return (isset($_REQUEST[$label])) ? $_REQUEST[$label] : $default;
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
		return $this->remoteAddr;
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
