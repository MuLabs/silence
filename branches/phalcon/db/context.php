<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

class Context extends Kernel\Core
{
	protected $name;
	protected $parameters;
	protected $isReadOnly;

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @throws Exception
	 */
	public function getParameter($name)
	{
		if (!isset($this->parameters[$name])) {
			throw new Exception($name, Exception::INVALID_PARAMETER);
		}

		return $this->parameters[$name];
	}

	/**
	 * @return bool
	 */
	public function isReadOnly()
	{
		return $this->isReadOnly;
	}

	/**
	 * @param string $name
	 * @param array $parameters
	 * @param bool $readOnly
	 */
	public function __construct($name, $parameters = array(), $readOnly = false)
	{
		$this->name = $name;
		$this->parameters = $parameters;
		$this->isReadOnly = (bool)$readOnly;
	}
}