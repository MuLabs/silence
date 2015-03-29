<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const FILE_NOT_FOUND = 1;
	const NOT_FOUND = 2;
	const FORMAT_NOT_FOUND = 3;
	const MISSING_PARAMETER = 4;
	const MISSING_SITE_SERVICE = 5;

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
			case self::MISSING_SITE_SERVICE:
				return 'Missing site service on application, add it or remove "siteIn" or "siteOut" instructions';
				break;
		}
		return parent::getFormatedMessage();
	}
}

