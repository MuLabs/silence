<?php
namespace Beable\Kernel\Db\Fake;

use Beable\Kernel;

/**
 * @namespace Beable\Kernel\Db\Fake;
 */
class Result extends Kernel\Db\Result
{

	/**
	 * @param Kernel\Db\Fake\Handler $handler
	 * @param string $query
	 */
	public function __construct(Handler $handler, $query)
	{
		$this->handler = $handler;
		$this->query = $query;
	}

	/**
	 * @return Handler
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchRow()
	{
		return array($this->query);
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchArray()
	{
		return array($this->query);
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchValue()
	{
		return $this->query;
	}

	/**
	 * {@inheritDoc}
	 */
	public function numRows()
	{
		return rand(0, 100);
	}

	/**
	 * {@inheritDoc}
	 */
	public function affectedRows()
	{
		return rand(0, 100);
	}

	/**
	 * {@inheritDoc}
	 */
	public function freeResult()
	{

	}
}
