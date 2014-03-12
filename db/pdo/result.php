<?php
namespace Mu\Kernel\Db\PDO;

use Mu\Kernel;

/**
 * @namespace Mu\Kernel\Db\PDO;
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
		return $this->ressource ? $this->ressource->fetch(\PDO::FETCH_NUM) : null;
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
	 * @return array
	 */
	public function fetchAllValue()
	{
		$values = array();
		while ($row = $this->fetchValue()) {
			$values[] = $row;
		}

		return $values;
	}

	/**
	 * @return array
	 */
	public function fetchAll()
	{
		$values = array();
		while ($row = $this->fetchArray()) {
			$values[] = $row;
		}

		return $values;
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
