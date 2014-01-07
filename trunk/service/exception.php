<?php
namespace Mu\Kernel\Service;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const NOT_FOUND = 0;
	const SERVICE_REGISTRATION_ERROR = 1;
	const CONTEXT_NOT_FOUND = 2;
	const HANDLER_TYPE_NOT_FOUND = 3;
	const HANDLER_CREATION_ERROR = 4;
	const HANDLER_CLOSURE_ERROR = 5;
	const CONTEXT_ALREADY_EXISTS = 6;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::NOT_FOUND:
				return 'Service not found : ' . $this->message;
				break;
			case self::SERVICE_REGISTRATION_ERROR:
				return 'Service registration failure -- ' . $this->message;
				break;
			case self::CONTEXT_NOT_FOUND:
				return 'Service context not found -- ' . $this->message;
				break;
			case self::HANDLER_TYPE_NOT_FOUND:
				return 'Handler type not found -- ' . $this->message;
				break;
			case self::HANDLER_CREATION_ERROR:
				return 'Handler creation failed -- ' . $this->message;
				break;
			case self::HANDLER_CLOSURE_ERROR:
				return 'Handler destruction failed -- ' . $this->message;
				break;
			case self::CONTEXT_ALREADY_EXISTS:
				return 'Context name is already registered -- ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}
