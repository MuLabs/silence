<?php
namespace Mu\Kernel\Controller;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const FILE_NOT_FOUND = 1;
	const CLASS_NOT_FOUND = 2;
	const INVALID_FRAGMENT_NAME = 3;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::FILE_NOT_FOUND:
				return 'Controller file not found : ' . $this->message;
				break;
			case self::CLASS_NOT_FOUND:
				return 'Controller class not found : ' . $this->message;
				break;
			case self::INVALID_FRAGMENT_NAME:
				return 'Invalid fragment name : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

