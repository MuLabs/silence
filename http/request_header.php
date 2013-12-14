<?php
namespace Beable\Kernel\Http;

use Beable\Kernel;

class Request_header
{
	private $accept;
	private $acceptCharset;
	private $acceptEncoding;
	private $acceptLanguage;
	private $connection;
	private $host;
	private $referer;
	private $userAgent;
	private $https;

	private function __clone()
	{
	}

	public function __destruct()
	{
	}

	/**
	 * @return string
	 */
	public function getAccept()
	{
		return $this->accept;
	}

	/**
	 * @return string
	 */
	public function getAcceptCharset()
	{
		return $this->acceptCharset;
	}

	/**
	 * @return string
	 */
	public function getAcceptEncoding()
	{
		return $this->acceptEncoding;
	}

	/**
	 * @return string
	 */
	public function getAcceptLanguage()
	{
		return $this->acceptLanguage;
	}

	/**
	 * @return string
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getReferer()
	{
		return $this->referer;
	}

	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return $this->userAgent;
	}

	/**
	 * @return string
	 */
	public function getHttps()
	{
		return $this->https;
	}
}
