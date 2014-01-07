<?php
namespace Mu\Kernel;

use Mu\Kernel;

class Exception extends \Exception
{
	const UNDEFINED_ACTION = 1;
	const CONSOLE_EXPECTED = 2;
	const NO_STATIC_REGISTRED = 3;

	public function display()
	{
		echo get_called_class() . ' : ' . $this->getFormatedMessage() . "<br />\n";
	}

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::UNDEFINED_ACTION:
				return 'Undefined action : ' . $this->message;
				break;
			case self::CONSOLE_EXPECTED:
				return 'Console mode expected';
				break;
			case self::NO_STATIC_REGISTRED:
				return 'No static URL defined please use "registerStatic" function';
				break;
			default:
				return 'Undefined error : ' . $this->message;
				break;
		}
	}
}

