<?php
namespace Mu\Kernel\File;

use Mu\Kernel;
use Mu\Kernel\Http;

abstract class Handler extends Kernel\Handler\Core
{
	const DEFAULT_SEPARATOR_LINE = "\n";
	const DEFAULT_SEPARATOR_STRING = '"';
	const DEFAULT_SEPARATOR_VALUE = ',';

	protected $configPrefix = 'file_';
	protected $sep_line = self::DEFAULT_SEPARATOR_LINE;
	protected $sep_string = self::DEFAULT_SEPARATOR_STRING;
	protected $sep_value = self::DEFAULT_SEPARATOR_VALUE;
	protected $content = array();
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
		$formated = $this->format($line);
		if ($formated === false) {
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
	 * Open file and store datas
	 * @param $file
	 * @param array $parameters
	 * @throws Exception
	 */
	public function open($file, array $parameters = array())
	{
		if (!file_exists($file)) {
			throw new Exception($file, Exception::FILE_NOT_EXISTS);
		}

		// Parse parameters:
		$this->setParameters($parameters);

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
				$this->add(trim($line));
			}
		}

		// Close handle:
		fclose($handle);
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
			throw new Exception($name, Exception::FILE_NOT_WRITABE);
		}

		// Output headers:
		if (is_array($header)) {
			$this->writeLine($handle, $this->toString($header));
		}

		// Output content:
		foreach ($this->content as $line) {
			$this->writeLine($handle, $this->toString($line));
		}

		// Close file handler:
		fclose($handle);
	}

	/**
	 * Send content from server
	 * @param $name
	 * @throws \Mu\Kernel\EndException
	 */
	public function send($name)
	{
		// Get content:
		$content = '';
		foreach ($this->content as $line) {
			$content .= $this->toString($line) . $this->sep_line;
		}

		// Set headers:
		$http = new Http\Response();
		$http->getHeader()->setContentType($this->getMimeType());
		$http->getHeader()->setContentLength(strlen($content));
		$http->getHeader()->setContentFilename($name);

		// Write content:
		$http->setContent($content);
		$http->send();

		// Exite if needed:
		throw new Kernel\EndException();
	}

	/**
	 * @param array $parameters
	 */
	public function setParameters(array $parameters = array())
	{
		if (isset($parameters['sep_line']) && is_string($parameters['sep_line'])) {
			$this->sep_line = $parameters['sep_line'];
		}
		if (isset($parameters['sep_string']) && is_string($parameters['sep_string'])) {
			$this->sep_string = $parameters['sep_string'];
		}
		if (isset($parameters['sep_value']) && is_string($parameters['sep_value'])) {
			$this->sep_value = $parameters['sep_value'];
		}
	}

	/**
	 * Correctly format a line to string
	 * @param mixed $line line content to output
	 * @return string
	 */
	protected function toString($line)
	{
		if (is_array($line)) {
			$line = implode($this->sep_value, $line);
		}

		return $line;
	}

	/**
	 * Correctly format a line for the handler storage
	 * @param $line
	 * @return mixed
	 */
	abstract protected function format($line);

	/**
	 * Get mime type from Http\Header\Response
	 * @return string
	 */
	abstract protected function getMimeType();

	/**
	 * Call the correct write function to add line into handle
	 * @param $handle
	 * @param $line
	 * @return mixed
	 */
	abstract protected function writeLine($handle, $line);
}
