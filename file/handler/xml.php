<?php
namespace Beable\Kernel\File\Handler;

use Beable\Kernel;
use Beable\Kernel\Http;
use Beable\Kernel\File\Exception;

class Xml extends Kernel\File\Handler
{
	const DEFAULT_NODE = 'root';

	/** @var \SimpleXMLElement $content */
	protected $content;

	/**
	 * {@inheritDoc}
	 */
	public function __init()
	{
		$this->clean();
	}

	/**
	 * Not implemented
	 * @param $line
	 * @throws Exception
	 */
	public function add($line)
	{
		throw new Exception('addChild', Exception::FUNCTION_NOT_IMPLEMENT);
	}

	/**
	 * @param $name
	 * @param null $value
	 * @param null $namespace
	 */
	public function addAttribute ($name, $value = null, $namespace = null)
	{
		$this->content->addAttribute($name, $value, $namespace);
	}

	/**
	 * @param $name
	 * @param null $value
	 * @param null $namespace
	 * @return \SimpleXMLElement
	 */
	public function addChild ($name, $value = null, $namespace = null)
	{
		return $this->content->addChild($name, $value, $namespace);
	}

	/**
	 * Not implemented
	 * @param array $lines
	 * @throws Exception
	 */
	public function addMultiple($lines = array())
	{
		throw new Exception('addChild', Exception::FUNCTION_NOT_IMPLEMENT);
	}

	/**
	 * Clean current content
	 */
	public function clean()
	{
		$this->load(
			'<'.self::DEFAULT_NODE.'>
			</'.self::DEFAULT_NODE.'>'
		);
	}

	/**
	 * @param null $ns
	 * @param bool $is_prefix
	 * @return \SimpleXMLElement
	 */
	public function children ($ns = null, $is_prefix = false)
	{
		return $this->content->children($ns, $is_prefix);
	}

	/**
	 * Returns namespaces used in document
	 * @param bool $recursive
	 * @return array
	 */
	public function getNamespaces ($recursive = false)
	{
		return $this->content->getNamespaces($recursive);
	}

	/**
	 * @param bool $recursive
	 * @return array
	 */
	public function getDocNamespaces ($recursive = false)
	{
		return $this->content->getDocNamespaces($recursive);
	}

	/**
	 * Open file and store datas
	 * @param $file
	 * @param array $parameters
	 * @throws Exception
	 */
	public function open($file, array $parameters = array())
	{
		if ($file!='' && !file_exists($file)) {
			throw new Exception($file, Exception::FILE_NOT_EXISTS);
		}

		// Read file:
		$data = '';
		if (!$handle = @fopen($file, 'r')) {
			throw new Exception($file, Exception::FILE_NOT_READABLE);
		}
		// Store data:
		while (!feof($handle)) {
			$line = fgets($handle);
			if ($line !== false) {
				$data.= $line;
			}
		}

		// Close handle:
		fclose($handle);

		// Load document:
		try {
			$this->load($data);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * @param $data
	 * @param int $options
	 * @param bool $data_is_url
	 * @param string $ns
	 * @param bool $is_prefix
	 * @throws Exception
	 */
	public function load($data, $options = 0, $data_is_url = false, $ns = '', $is_prefix = false)
	{
		try {
			$this->content = new \SimpleXMLElement($data, $options, $data_is_url, $ns, $is_prefix);
			if (!$this->content) {
				throw new Exception('xml', Exception::INCORRECT_FORMAT);
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), Exception::INCORRECT_FORMAT);
		}
	}

	/**
	 * Save XML into a file
	 * @param $name
	 * @param array $header
	 * @throws Exception
	 */
	public function save($name, $header = array())
	{
		if (!$this->content->saveXML($name)) {
			throw new Exception($file, Exception::FILE_NOT_WRITABE);
		}
	}

	/**
	 * Send content from server
	 * @param $name
	 */
	public function send($name)
	{
		// Get content:
		$content = $this->content->saveXML();

		// Set headers:
		$http = new Http\Response();
		$http->getHeader()->setContentType($this->getMimeType());
		$http->getHeader()->setContentLength(strlen($content));
		$http->getHeader()->setContentFilename($name);

		// Write content:
		$http->setContent($content);
		$http->send();

		// Exite if needed:
		exit();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function format($line)
	{
	}

	/**
	 * Get mime type from Http\Response_header
	 * @return string
	 */
	protected function getMimeType()
	{
		return \Beable\Kernel\Http\Response_header::MIME_TYPE_XML;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function writeLine($handle, $line)
	{
	}
}
