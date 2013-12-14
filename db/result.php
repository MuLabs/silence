<?php
namespace Beable\Kernel\Db;

use Beable\Kernel;

/**
 * @namespace Beable\Kernel\Db
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
