<?php
namespace Beable\Kernel\Route;

use Beable\Kernel;

class Exception extends Kernel\Exception
{
	const FILE_NOT_FOUND = 1;
	const NOT_FOUND = 2;
	const FORMAT_NOT_FOUND = 3;
	const MISSING_PARAMETER = 4;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::FILE_NOT_FOUND:
				return 'Routing Error, route file not found : ' . $this->message;
				break;
			case self::NOT_FOUND:
				return 'Routing Error, route not found : ' . $this->message;
				break;
			case self::FORMAT_NOT_FOUND:
				return 'Error, unknow format : ' . $this->message;
				break;
			case self::MISSING_PARAMETER:
				return 'Missing parameter for url construction : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

