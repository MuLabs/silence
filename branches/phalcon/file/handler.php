<?php
namespace Mu\Kernel\File;

use Mu\Kernel;
use Mu\Kernel\Http;

abstract class Handler extends Kernel\Handler\Core
{
	const DEFAULT_SEPARATOR_LINE = "\n";
	const DEFAULT_SEPARATOR_VALUE = ',';
	const DEFAULT_PROTECTOR_VALUE = '"';

	protected $configPrefix = 'file_';
	protected $sepLine = self::DEFAULT_SEPARATOR_LINE;
	protected $sepValue = self::DEFAULT_SEPARATOR_VALUE;
	protected $proValue = self::DEFAULT_PROTECTOR_VALUE;
	protected $content = array();
	protected $appendHandler = array();
	protected $filename;

	/**
	 * Add a line
	 * @param $line
	 * @throws Exception
	 */
	public function add($line)
	{
		$this->content[] = $this->format($line);
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
	 * @return array
	 */
	public function getContent() {
		return $this->content;
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
		$firstLine = true;
		while (!feof($handle)) {
			$line = $this->openLine($handle);

			if ($line !== false && strlen($line)) {
				if ($firstLine) {
					$firstLine = false;
					if (substr($line, 0, 3) === $this->getBOM()) {
						$line = substr($line, 3);
					}
				}

				$this->add(trim($line));
			}
		}

		// Close handle:
		fclose($handle);
	}

	/**
	 * @param $handle
	 * @return string
	 */
	protected function openLine($handle) {
		return trim(fgets($handle));
	}

	/**
	 * @return string
	 */
	public function getBOM() {
		return chr(239) . chr(187) . chr(191);
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
			$this->writeLine($handle, $header);
		}

		// Output content:
		foreach ($this->content as $line) {
			$this->writeLine($handle, $line);
		}

		// Close file handler:
		fclose($handle);
	}

	/**
	 * Append content into a file
	 * @param $name
	 * @throws Exception
	 */
	public function append($name)
	{
		if (!isset($this->appendHandler[$name])) {
			$this->appendHandler[$name] = @fopen($name, 'a');
		}

		if (!$this->appendHandler[$name]) {
			throw new Exception($name, Exception::FILE_NOT_WRITABE);
		}

		// Output content:
		foreach ($this->content as $line) {
			$this->writeLine($this->appendHandler[$name], $line);
		}
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
			$content .= $this->proValue . $this->toString($line) . $this->proValue . $this->sepLine;
		}

		// Set headers:
		$http = new Http\Response();
		$http->getHeader()->setContentType($this->getMimeType());
		$http->getHeader()->setContentLength(strlen($content));
		$http->getHeader()->setContentFilename($name);

		// Write content:
		$http->setContent($content);
		$http->send();
	}

	/**
	 * Correctly format a line to string
	 * @param mixed $line line content to output
	 * @return string
	 */
	protected function toString($line)
	{
		if (is_array($line)) {
			$line = implode($this->sepValue, $line);
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
	abstract public function getMimeType();

	/**
	 * Call the correct write function to add line into handle
	 * @param $handle
	 * @param $line
	 * @return mixed
	 */
	abstract protected function writeLine($handle, $line);
}
