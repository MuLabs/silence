<?php
namespace Beable\Kernel\File;

use Beable\Kernel;
use Beable\Kernel\Http;

abstract class Handler extends Kernel\Handler\Core
{
	const SEPARATOR_LINE  = "\n";
	const SEPARATOR_VALUE = ',';
	const SEPARATOR_STRING= '"';

	protected $configPrefix = 'file_';
	private $content = array();
	private $filename;

	/**
	 * {@inheritDoc}
	 */
	public function __init()
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function __close()
	{
	}

	/**
	 * Add a line
	 * @param $line
	 * @throws Exception
	 */
	public function add($line)
	{
		$formated = $this->formatStored(trim($line));
		if (!$formated) {
			throw new Exception($line, Exception::INCORRECT_FORMAT);
		}
		$this->content[] = $formated;
	}

	/**
	 * Add an array of lines
	 * @param array $lines
	 */
	public function addMultiple($lines = array())
	{
		foreach ($lines as $line) {
			$this->add($line);
		}
	}

	/**
	 * Clean current content
	 */
	public function clean()
	{
		$this->content = array();
	}

	/**
	 * Save content and headers into a file
	 * @param $name
	 * @param array $header
	 * @throws Exception
	 */
	public function save($name, $header = array())
	{
		$handle = @fopen($name, 'w');
		if (!$handle) {
			throw new Exception($file, Exception::FILE_NOT_WRITABE);
		}

		// Output headers:
		if (is_array($header)) {
			$this->formatOutput($handle, $header, true);
		}

		// Output content:
		foreach ($this->content as $line) {
			$this->formatOutput($handle, $line);
		}

		// Close file handler:
		fclose($fp);
	}

	/**
	 * Send content from server
	 * @param $name
	 */
	public function send($name)
	{
		$http = new Http\Response();
		$http->getHeader()->setContentType($this->getMimeType());
		$http->getHeader()->setContentLength(strlen($this->content));
		$http->getHeader()->setContentFilename($name);

		$http->setContent(implode(self::SEPARATOR_LINE, $this->content));
		$http->send();
		exit();
	}

	/**
	 * Open file and store datas
	 * @param $file
	 * @throws Exception
	 */
	public function open($file)
	{
		if (!file_exists($file)) {
			throw new Exception($file, Exception::FILE_NOT_EXISTS);
		}

		// Store current filename:
		$this->filename = $file;
		// Read file:
		if (!$handle = @fopen($file, 'r')) {
			throw new Exception($file, Exception::FILE_NOT_READABLE);
		}
		// Store data:
		while (!feof($handle)) {
			$line = fgets($handle);
			if ($line !== false) {
				$this->add($line);
			}
		}

		// Close handle:
		fclose($handle);
	}

	/**
	 * Correctly format a line for the handler storage
	 * @param $line
	 * @return mixed
	 */
	abstract protected function formatStored($line);

	/**
	 * Correctly format a line for output
	 * @param mixed $handle	file handle
	 * @param mixed $line	line content to output
	 * @param bool $header	Is this line is the header one
	 */
	abstract protected function formatOutput($handle, $line, $header = false);

	/**
	 * Get mime type from Http\Response_header
	 * @return string
	 */
	abstract protected function getMimeType();
}
