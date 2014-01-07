<?php
namespace Mu\Kernel\Service;

use Mu\App\Application;
use Mu\Kernel;
use Mu\Kernel\Handler;

abstract class Extended extends Core
{
	/** @var Handler\Core[] $handlers */
	private $handlers = array();
	private $logs = array();

	abstract protected function getNamespace();

	abstract protected function getDirectory();

	/**
	 * Correctly fire handlers close method
	 */
	public function __destruct()
	{
		try {
			$handlers = $this->getHandlers();
			foreach ($handlers as $handler) {
				$handler->__close();
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), Exception::HANDLER_CLOSURE_ERROR);
		}
	}

	/**
	 * @param string $name
	 * @param Handler\Core $handler
	 * @return void
	 * @throws Exception
	 */
	public function addHandler($name, Handler\Core $handler)
	{
		if (!isset($this->handlers[$name])) {
			$this->handlers[$name] = $handler;
		} else {
			throw new Exception($name, Exception::CONTEXT_ALREADY_EXISTS);
		}
	}

	/**
	 * @return Handler\Core[]
	 */
	public function getHandlers()
	{
		return $this->handlers;
	}

	/**
	 * Return an handler by its type and context, generate it if needed
	 * @param string $type
	 * @param string $context
	 * @throws Exception
	 * @return Handler\Core
	 */
	public function getHandler($type, $context = Handler\Core::DEFAULT_CONTEXT)
	{
		// Generate handler if needed:
		if (!isset($this->handlers[$context])) {
			$this->addHandler($context, $this->generateHandler($type, $context));
		}

		return $this->handlers[$context];
	}

	/**
	 * Return an handler by its context label.
	 * Note that this function doesn't generate it.
	 * @param string $context
	 * @throws Exception
	 * @return Handler\Core
	 */
	public function getHandlerByContext($context)
	{
		if (!isset($this->handlers[$context])) {
			throw new Exception($context, Exception::CONTEXT_NOT_FOUND);
		}

		return $this->handlers[$context];
	}

	/**
	 * @return array
	 */
	public function getLogs()
	{
		return $this->logs;
	}

	/**
	 * Generate an handler of a given type with a given context
	 * @param string $type
	 * @param string $context
	 * @return Handler\Core
	 * @throws Exception
	 */
	public function generateHandler($type, $context = Handler\Core::DEFAULT_CONTEXT)
	{
		// Test if setApp as been correctly done:
		if (!$this->getApp() instanceof Application) {
			throw new Exception('Application is not defined, please register the service first or use setApp', Exception::SERVICE_REGISTRATION_ERROR);
		}

		// Get handler class
		$type = ucfirst($type);
		$class = $this->getNamespace() . '\\Handler\\' . $type;

		try {
			// Generate new handler and initialize it
			/** @var Handler\Core $handler */
			$handler = new $class();
			$handler->setApp($this->getApp());
			$handler->setContext($context);
			$handler->__init();

			// Return the handler instance:
			return $handler;
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), Exception::HANDLER_TYPE_NOT_FOUND);
		}
	}

	/**
	 * @param string $message
	 * @return void
	 */
	public function log($message)
	{
		$this->logs[] = $message;
	}
}
