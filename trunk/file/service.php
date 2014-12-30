<?php
namespace Mu\Kernel\File;

use Mu\Kernel;

/**
 * Class Service
 * @package Mu\Kernel\File
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
	 * @param array $parameters
	 * @param $handler Get handler name or try to determine it by extension (if set to null)
	 * @return Handler
	 * @throws Exception
	 */
	public function open($file, array $parameters = array(), $handler = null)
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
			$handler->open($file, $parameters);

			// Return handler:
			return $handler;
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNamespace()
	{
		return __NAMESPACE__;
	}
}
