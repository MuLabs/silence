<?php
namespace Beable\Kernel\Http;

use Beable\Kernel;

class Response
{
	private $header;
	private $content;

	public function __construct()
	{
		$this->header = new Response_header();
	}

	private function __clone()
	{
	}

	public function __destruct()
	{
	}

	/**
	 * @param Response_header $header
	 */
	public function setHeader(Response_header $header)
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
	 * @return Response_header
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
