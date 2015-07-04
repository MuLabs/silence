<?php
namespace Mu\Kernel\Cache;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const KEY_NOT_FOUND = 1;
	const FAILED_TO_CONNECT = 2;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::KEY_NOT_FOUND:
				return 'Key not found : ' . $this->message;
				break;
			case self::FAILED_TO_CONNECT:
				return 'Failed to connect to cache : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

