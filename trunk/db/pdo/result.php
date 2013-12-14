<?php
namespace Beable\Kernel\Db\PDO;

use Beable\Kernel;

/**
 * @namespace Beable\Kernel\Db\PDO;
 */
class Result extends Kernel\Db\Result
{
	private $ressource;

	/**
	 * @param Handler $handler
	 * @param \PDOStatement $ressource
	 */
	public function __construct(Handler $handler, \PDOStatement $ressource = null)
	{
		$this->ressource = $ressource;
		$this->handler = $handler;
	}

	/**
	 * @return \mysqli_result
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
		return $this->ressource ? $this->ressource->fetch() : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchArray()
	{
		return $this->ressource ? $this->ressource->fetch(\PDO::FETCH_ASSOC) : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchValue()
	{
		$line = $this->ressource ? $this->ressource->fetch(\PDO::FETCH_NUM) : null;
		if (is_array($line)) {
			return reset($line);
		} else {
			return null;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function numRows()
	{
		return $this->ressource ? $this->ressource->rowCount() : 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function affectedRows()
	{
		return $this->numRows();
	}

	/**
	 * {@inheritDoc}
	 */
	public function freeResult()
	{
		$this->ressource->closeCursor();
	}
}
