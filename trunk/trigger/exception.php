<?php
namespace Mu\Kernel\Trigger;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const TRIGGER_NOT_FOUND = 1;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::TRIGGER_NOT_FOUND:
				return 'Trigger not found : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

