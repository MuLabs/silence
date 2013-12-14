<?php
namespace Beable\Kernel\View;

use Beable\Kernel;

class Exception extends Kernel\Exception
{
	const DIR_NOT_FOUND = 1;
	const CACHE_DIR_NOT_FOUND = 2;
	const COMPILE_DIR_NOT_FOUND = 3;
	const TARGET_NOT_FOUND = 4;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::DIR_NOT_FOUND:
				return 'View dir not found : ' . $this->message;
				break;
			case self::CACHE_DIR_NOT_FOUND:
				return 'Cache dir not found : ' . $this->message;
				break;
			case self::COMPILE_DIR_NOT_FOUND:
				return 'Compile dir not found : ' . $this->message;
				break;
			case self::TARGET_NOT_FOUND:
				return 'Target file not found : ' . $this->message;
				break;
		}
		return 'Undefined error : ' . $this->message;
	}
}