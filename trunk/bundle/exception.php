<?php
namespace Beable\Kernel\Bundle;

use Beable\Kernel;

class Exception extends Kernel\Exception
{
	const NOT_FOUND = 1;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::NOT_FOUND:
				return 'Bundle not found : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}
