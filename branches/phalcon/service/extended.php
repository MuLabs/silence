<?php
namespace Mu\Kernel\Service;

use Mu\Kernel;

abstract class Extended extends Core
{
	/** @var Kernel\Handler\Core[] $handlers */
	protected $handlers = array();
	protected $logs = array();

	abstract protected function getNamespace();

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
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @param string $name
	 * @param Kernel\Handler\Core $handler
	 * @return void
	 * @throws Exception
	 */
	public function addHandler($name, Kernel\Handler\Core $handler)
	{
		if (!isset($this->handlers[$name])) {
			$this->handlers[$name] = $handler;
		}
	}

	/**
	 * @return Kernel\Handler\Core[]
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
	 * @return Kernel\Handler\Core
	 */
	public function getHandler($type, $context = Kernel\Handler\Core::DEFAULT_CONTEXT)
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
	 * @return Kernel\Handler\Core
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
	 * @return Kernel\Handler\Core
	 * @throws Exception
	 */
	public function generateHandler($type, $context = Kernel\Handler\Core::DEFAULT_CONTEXT)
	{
		// Test if setApp as been correctly done:
		if (!$this->getApp() instanceof Kernel\Application) {
			throw new Exception('Application is not defined, please register the service first or use setApp', Exception::SERVICE_REGISTRATION_ERROR);
		}

		// Get handler class
		$type = ucfirst($type);
		$class = $this->getNamespace() . '\\Handler\\' . $type;
		if (!class_exists($class)) {
			throw new Exception('Class not found '.$class, Exception::HANDLER_TYPE_NOT_FOUND);
		}

		try {
			// Generate new handler and initialize it
			/** @var Kernel\Handler\Core $handler */
			$handler = new $class();
			$handler->setApp($this->getApp());
			$handler->setContext($context);
			$handler->__init();

			// Return the handler instance:
			return $handler;
		} catch (\Exception $e) {
			throw new Exception($e->getMessage(), Exception::HANDLER_CREATION_ERROR);
		}
	}
}
