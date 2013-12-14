<?php
namespace Beable\Kernel\Db\Fake;

use Beable\Kernel;

class Service extends Kernel\Db\Service
{
	/**
	 * @param $contextName
	 * @return Handler
	 */
	protected function generateHandler($contextName)
	{
		return new Handler();
	}

	/**
	 * @return Executor
	 */
	protected function generateExecutor()
	{
		return new Executor();
	}

	/**
	 * @param $query
	 * @return array
	 */
	public function fetchRow($query)
	{
		return false;
	}

	/**
	 * @param $query
	 * @return bool
	 */
	public function freeResult($query)
	{
		return null;
	}

	/**
	 * @param $query
	 * @return int
	 */
	public function numRows($query)
	{
		return 0;
	}
}
