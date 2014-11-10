<?php
namespace Mu\Kernel\Trigger;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	protected $triggerFunctions = array();
    protected $triggerInstanceFunctions = array();

    /**
	 * @param string $name
     * @param array $function
     * @return string
	 */
	public function register($name, $function)
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
            foreach ($this->triggerFunctions[$name] as $key => $oneTrigger) {
                if (!isset($this->triggerInstanceFunctions[$name][$key])) {
                    list($type, $objectName, $functionName) = $oneTrigger;

                    if ($type == 'manager') {
                        $object = $this->getApp()->getModelManager()->getOneManager($objectName);
                    } else {
                        $object = $this->getApp()->getServicer()->get($objectName);
                    }

                    if (!$object) {
                        continue;
                    }

                    $this->triggerInstanceFunctions[$name][$key] = array($object, $functionName);
                }

                $oneTrigger = $this->triggerInstanceFunctions[$name][$key];
                if (!is_callable($oneTrigger)) {
					continue;
				}

				call_user_func($oneTrigger, $params);
			}
		} else {
			//throw new Exception($name, Exception::TRIGGER_NOT_FOUND);
		}
	}
}