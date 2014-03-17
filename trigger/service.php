<?php
namespace Mu\Kernel\Trigger;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	protected $triggerFunctions = array();

	/**
	 * @param string $name
	 * @param callable $function
	 * @return string
	 */
	public function register($name, callable $function)
	{
		$this->triggerFunctions[$name][] = $function;
	}

	/**
	 * @param string $name
	 * @param array $params
	 * @throws Exception
	 */
	public function call($name, array $params)
	{
		if (isset($this->triggerFunctions[$name]) && is_array($this->triggerFunctions[$name])) {
			foreach ($this->triggerFunctions[$name] as $oneTrigger) {
				call_user_func($oneTrigger, $params);
			}
		} else {
			throw new Exception($name, Exception::TRIGGER_NOT_FOUND);
		}
	}
}