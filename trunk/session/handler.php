<?php
namespace Beable\Kernel\Session;

use Beable\Kernel;

abstract class Handler extends Kernel\Handler\Core
{
	protected $config_prefix = 'ses_';

	/**
	 * Clean current handler values
	 * This function is an alias of $this->setAll()
	 */
	abstract public function clean();

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
