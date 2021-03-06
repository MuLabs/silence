<?php
namespace Mu\Kernel\File;

use Mu\Kernel;

class Exception extends Kernel\Service\Exception
{
	const FILE_NOT_EXISTS = 100;
	const INCORRECT_FORMAT = 101;
	const FILE_NOT_READABLE = 102;
	const FILE_NOT_WRITABE = 103;
	const FUNCTION_NOT_IMPLEMENT = 104;
	const FUNCTION_NOT_EXITS = 105;

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
			case self::FUNCTION_NOT_IMPLEMENT:
				return 'Function not implement for this handler, use ' . $this->message . ' instead';
				break;
			case self::FUNCTION_NOT_EXITS:
				return 'Function not exists for this handler -- ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}
