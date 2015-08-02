<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

/**
 * @namespace Mu\Kernel\Db
 */
abstract class Result
{
	/**
	 * @return    array
	 */
	abstract public function fetchRow();

	/**
	 * Fetch a result row as an associative, a numeric array, or both
	 *
	 * @return    array Array with query results
	 */
	abstract public function fetchArray();

	/**
	 * @return    mixed
	 */
	abstract public function fetchValue();

	/**
	 * @return array
	 */
	abstract public function fetchAllValue();

	/**
	 * @return array
	 */
	abstract public function fetchAll();

	/**
	 * Get number of rows return by result
	 *
	 * @return int
	 */
	abstract public function numRows();

	/**
	 * Get number of rows affected by query
	 *
	 * @return int
	 */
	abstract public function affectedRows();

	/**
	 * Free memory used by query result
	 */
	abstract public function freeResult();
}
