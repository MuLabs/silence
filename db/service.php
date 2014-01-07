<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

abstract class Service extends Kernel\Service\Core
{
	private $logs = array();
	private $handlers = array();
	private $contexts = array();

	/**
	 * @param string $name
	 * @param Handler $handler
	 * @return void
	 */
	public function addHandler($name, Handler $handler)
	{
		$this->handlers[$name] = $handler;
	}

	/**
	 * @return Handler[]
	 */
	public function getHandlers()
	{
		return $this->handlers;
	}

	/**
	 * @param string $name
	 * @throws Exception
	 * @return Handler
	 */
	public function getHandler($name)
	{
		if (!isset($this->handlers[$name])) {
			$this->handlers[$name] = $this->generateHandler($name);
		}
		return $this->handlers[$name];
	}

	/**
	 * @param Context $context
	 */
	public function registerContext(Context $context)
	{
		$this->contexts[$context->getName()] = $context;
	}

	/**
	 * @param $name
	 * @return mixed
	 * @throws Exception
	 */
	public function getContext($name)
	{
		if (!isset($this->contexts[$name])) {
			throw new Exception($name, Exception::CONTEXT_NOT_FOUND);
		}
		return $this->contexts[$name];
	}

	/**
	 * @return array
	 */
	public function getLogs()
	{
		return $this->logs;
	}

	/**
	 * @param string $contextName
	 * @return Handler
	 */
	abstract protected function generateHandler($contextName);

	/**
	 * @param string $query
	 * @return void
	 */
	public function log($query)
	{
		$this->logs[] = $query;
	}
}
