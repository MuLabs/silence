<?php
namespace Beable\Kernel\File;

use Beable\Kernel;

/**
 * Class Service
 * @package Beable\Kernel\File
 * @author Olivier Stahl
 */
class Service extends Kernel\Service\Extended
{
	const DEFAULT_HANDLER = 'text';

	/**
	 * Remove file
	 * @param $file
	 * @throws Exception
	 */
	public function delete($file)
	{
		if (!file_exists($file)) {
			throw new Exception($file, Exception::FILE_NOT_EXISTS);
		}

		unlink($file);
	}

	/**
	 * Open a file and generate an handler of the correct type
	 * @param $file
	 * @param $handler  Get handler name or try to determine it by extension (if set to null)
	 * @return Handler
	 * @throws Exception
	 */
	public function open($file, $handler = null)
	{
		try {
			// Determine if an handler exists:
			if ($handler === null) {
				$ext = (preg_match('#\.(\w+)$#', $file, $match)) ? $match[1] : '';
				$handler = (file_exists(__DIR__ . '/handler/' . $ext . '.php')) ? $ext : self::DEFAULT_HANDLER;
			}

			// Get handler:
			/** @var Handler $handler */
			$handler = $this->getHandler($handler);
			$handler->open($file);

			// Return handler:
			return $handler;
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDirectory()
	{
		return 'file';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNamespace()
	{
		return '\\Beable\\Kernel\\File';
	}
}
