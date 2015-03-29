<?php
namespace Mu\Kernel\Backoffice;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const BREADCRUMB_NOT_FOUND 	= 0;
	const SECTION_NOT_FOUND 	= 1;
	const WIDGET_NOT_FOUND 		= 2;
	const WIDGET_NOT_ALLOWED 	= 3;
	const INVALID_WIDGET_OBJECT = 4;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::BREADCRUMB_NOT_FOUND:
				return 'Breadcrumb not found : ' . $this->message;
				break;
			case self::SECTION_NOT_FOUND:
				return 'Backoffice section not found : ' . $this->message;
				break;
			case self::WIDGET_NOT_FOUND:
				return 'Widget not found : ' . $this->message;
				break;
			case self::WIDGET_NOT_ALLOWED:
				return 'Widget are not allowed on this section : ' . $this->message;
				break;
			case self::INVALID_WIDGET_OBJECT:
				return 'Object widget Invalid : ' . $this->message;
				break;
			default:
				return parent::getFormatedMessage();
				break;
		}
	}
}