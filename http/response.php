<?php
namespace Mu\Kernel\Http;

use Mu\Kernel;

class Response
{
	protected $header;
	protected $content;

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
	 * Set response code
	 * @param $code
	 */
	public function setCode($code = 200)
	{
		$this->getHeader()->setCode($code);
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
		throw new Kernel\EndException();
	}
}
