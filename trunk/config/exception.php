<?php
namespace Mu\Kernel\Config;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const MISSING_MANDATORY_CONFIG = 1;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::MISSING_MANDATORY_CONFIG:
				return 'Missing mandatory configuration : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

