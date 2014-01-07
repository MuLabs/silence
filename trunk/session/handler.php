<?php
namespace Mu\Kernel\Session;

use Mu\Kernel;

abstract class Handler extends Kernel\Handler\Core
{
	protected $configPrefix = 'ses_';

	/**
	 * Delete the current session
	 * (clean + close)
	 */
	public function delete()
	{
		$this->clean();
		$this->save();
	}

	/**
	 * Clean current handler values
	 * This function is an alias of $this->setAll()
	 */
	public function clean()
	{
		$this->setAll();
	}

	/**
	 * Get a value if name exists in the handler context, else return a default value
	 * @param $name
	 * @param null $default
	 * @return mixed
	 */
	abstract public function get($name, $default = null);

	/**
	 * Get all handler values stored for this context
	 * @return array
	 */
	abstract public function getAll();

	/**
	 * Return current handler ID
	 * @return string
	 */
	abstract public function getId();

	/**
	 * Save the current session values
	 * @return void
	 */
	abstract public function save();

	/**
	 * Set a value in handler and return it
	 * @param $name
	 * @param null $value
	 * @return mixed
	 */
	abstract public function set($name, $value = null);

	/**
	 * Set all values stored into the context handler
	 * @param array $values
	 */
	abstract public function setAll($values = array());
}
