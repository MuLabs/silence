<?php
namespace Mu\Kernel\Backoffice;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const SECTION_NOT_FOUND = 1;
	const WIDGET_NOT_FOUND = 2;
	const WIDGET_NOT_ALLOWED = 2;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::SECTION_NOT_FOUND:
				return 'Backoffice section not found : ' . $this->message;
				break;
			case self::WIDGET_NOT_FOUND:
				return 'Widget not found : ' . $this->message;
				break;
			case self::WIDGET_NOT_ALLOWED:
				return 'Widget are not allowed on this section : ' . $this->message;
				break;
			default:
				return parent::getFormatedMessage();
				break;
		}
	}
}