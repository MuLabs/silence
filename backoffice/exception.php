<?php
namespace Mu\Kernel\Backoffice;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const SECTION_NOT_FOUND = 1;
	const WIDGET_NOT_FOUND = 2;

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
				return 'Widget section not found : ' . $this->message;
				break;
			default:
				return parent::getFormatedMessage();
				break;
		}
	}
}