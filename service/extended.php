<?php
namespace Beable\Kernel\Service;

use Beable\App\Application;
use Beable\Kernel;
use Beable\Kernel\Handler;

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
				$handler->close();
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
		}
		else {
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
		// Test if context already exists and handler type:
		if (isset($this->handlers[$context]) && $this->handlers[$context]->getClassName() != ucfirst($type)) {
			throw new Exception($context, Exception::CONTEXT_ALREADY_EXISTS);
		}

		// Generate handler if needed:
		if (!isset($this->handlers[$context])) {
			$this->handlers[$context] = $this->generateHandler($type);
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
	 * @param string $name
	 * @param string $context
	 * @return Handler\Core
	 * @throws Exception
	 */
	public function generateHandler($name, $context = Handler\Core::DEFAULT_CONTEXT)
	{
		// Test if setApp as been correctly done:
		if (!$this->getApp() instanceof Application) {
			throw new Exception('Application is not defined, please register the service first or use setApp', Exception::SERVICE_REGISTRATION_ERROR);
		}

		// Get handler class
		$type = ucfirst($name);
		$class= $this->getNamespace().'\\Handler\\'.$type;
		if (!is_file(__DIR__.'/../'.$this->getDirectory().'/handler/'.$name.'.php') || !class_exists($class)) {
			throw new Exception($type, Exception::HANDLER_TYPE_NOT_FOUND);
		}

		try {
			// Generate new handler and initialize it
			/** @var Handler\Core $handler */
			$handler = new $class();
			$handler->setApp($this->getApp());
			$handler->setContext($context);
			$handler->init();

			// Return the handler instance:
			return $handler;
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), Exception::HANDLER_CREATION_ERROR);
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