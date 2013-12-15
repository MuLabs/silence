<?php
namespace Beable\Kernel\File;

use Beable\Kernel;
use Beable\Kernel\Service;

class Exception extends Service\Exception
{
	const FILE_NOT_EXISTS 	= 100;
	const INCORRECT_FORMAT 	= 101;
	const FILE_NOT_READABLE	= 102;
	const FILE_NOT_WRITABE	= 103;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::FILE_NOT_EXISTS:
				return 'File doesn\'t exists -- ' . $this->message;
				break;
			case self::INCORRECT_FORMAT:
				return 'Incorrect format -- ' . $this->message;
				break;
			case self::FILE_NOT_READABLE:
				return 'Cannot read file -- ' . $this->message;
				break;
			case self::FILE_NOT_WRITABE:
				return 'Cannot write file -- ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}
