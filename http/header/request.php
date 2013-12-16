<?php
namespace Beable\Kernel\Http\Header;

use Beable\Kernel;

class Request
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
