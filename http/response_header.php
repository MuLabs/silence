<?php
namespace Beable\Kernel\Http;

use beable\Kernel;

class Response_header
{
	const MIME_TYPE_TEXT = 'text/plain';
	const MIME_TYPE_HTML = 'text/html';
	const MIME_TYPE_XML = 'text/xml';
	const MIME_TYPE_CSS = 'text/css';
	const MIME_TYPE_CSV = 'text/csv';
	const MIME_TYPE_JSON = 'application/json';

	const MIME_TYPE_JPEG = 'image/jpeg';
	const MIME_TYPE_JPG = 'image/jpg';
	const MIME_TYPE_GIF = 'image/gif';
	const MIME_TYPE_PNG = 'image/png';

	private $contentType = self::MIME_TYPE_HTML;
	private $contentLength;
	private $contentFilename;
	private $location;
	private $code = 200;

	public function __construct()
	{
	}

	public function __clone()
	{
	}

	public function __destruct()
	{
	}

	public function send()
	{
		$this->sendCode();
		if ($this->isSuccess()) {
			header('Content-type: ' . $this->contentType);

			if ($this->contentLength) {
				header('Content-length: ' . $this->contentLength);
			}

			if ($this->contentFilename) {
				header('Content-Disposition: attachment; filename="' . $this->contentFilename . '"');
			}

			if ($this->location) {
				header('Location: ' . $this->location);
			}
		}
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Expires: 0');
	}

	private function sendCode()
	{
		switch ($this->code) {
			case 200:
				header('HTTP/1.1 200 OK');
				break;

			case 301:
				header('HTTP/1.1 301 Moved Permanently');
				break;

			case 304:
				header('HTTP/1.1 304 Not Modified');
				break;

			case 401:
				header('HTTP/1.1 401 Unauthorized');
				break;

			case 403:
				header('HTTP/1.1 403 Forbidden');
				break;

			case 404:
				header('HTTP/1.1 404 Not Found');
				break;

			case 500:
				header('HTTP/1.1 500 Internal Server Error');
				break;
		}
	}

	public function isSuccess()
	{
		$code100 = floor($this->code / 100);

		return ($code100 == 2 || $code100 == 3);
	}

	/**
	 * @param int $code
	 */
	public function setCode($code)
	{
		$this->code = (int)$code;
	}

	/**
	 * @param int $type
	 */
	public function setContentType($type)
	{
		switch ($type) {
			case self::MIME_TYPE_HTML    :
			case self::MIME_TYPE_XML    :
			case self::MIME_TYPE_CSS    :
			case self::MIME_TYPE_CSV    :
			case self::MIME_TYPE_JSON    :

			case self::MIME_TYPE_JPG    :
			case self::MIME_TYPE_JPEG    :
			case self::MIME_TYPE_GIF    :
			case self::MIME_TYPE_PNG    :
				$this->contentType = $type;
				break;
		}
	}

	/**
	 * @param int $length
	 */
	public function setContentLength($length)
	{
		$this->contentLength = max(0, (int)$length);
	}

	/**
	 * @param string $filename
	 */
	public function setContentFilename($filename)
	{
		$this->contentFilename = $filename;
	}

	/**
	 * @param string $location
	 */
	public function setLocation($location)
	{
		$this->location = $location;
	}
}
