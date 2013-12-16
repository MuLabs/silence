<?php
namespace Beable\Kernel\Http;

use Beable\Kernel;

class Response
{
	private $header;
	private $content;

	public function __construct()
	{
		$this->header = new Header\Response();
	}

	private function __clone()
	{
	}

	public function __destruct()
	{
	}

	/**
	 * @param Header\Response $header
	 */
	public function setHeader(Header\Response $header)
	{
		$this->header = $header;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * @return Header\Response
	 */
	public function getHeader()
	{
		return $this->header;
	}

	public function send()
	{
		$this->header->send();

		if ($this->content) {
			echo $this->content;
		}
	}
}
