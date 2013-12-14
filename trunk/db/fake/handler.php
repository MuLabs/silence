<?php
namespace Beable\Kernel\Db\Fake;

use Beable\Kernel;

class Handler extends Kernel\Db\Handler
{
	/**
	 * {@inheritDoc}
	 */
	public function query($query)
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasError()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrors()
	{
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastError()
	{
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInsertId()
	{
		return rand(0, 100);
	}

	/**
	 * {@inheritDoc}
	 */
	public function commitTransaction()
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function startTransaction()
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rollbackTransaction()
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sendQuery(Kernel\Db\Query $query)
	{
		return new Result($this, $query->getQuery());
	}

	/**
	 * {@inheritDoc}
	 */
	protected function protectString($string)
	{
		return 'String protected [' . $string . ']';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStructureFromProperty(array $property)
	{
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStructureFromKey($tableToken, $keyName, array $key)
	{
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStructureFromTableInfos(array $tableInfos)
	{
		return '';
	}
}
