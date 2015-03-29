<?php
namespace Mu\Kernel\Bundle;

use Mu\Kernel;

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
