<?php
namespace Mu\Kernel\Communication;

use Mu\Kernel;
use Mu\Kernel\Service;

class Exception extends Service\Exception
{
	const INCORRECT_FORMAT_CONTENT = 100;
	const INCORRECT_FORMAT_DESTINATION = 101;
	const INCORRECT_FORMAT_ORIGIN = 102;
	const STATUS_NOT_READY = 103;
	const SENDING_FAILURE = 104;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::INCORRECT_FORMAT_CONTENT:
				return 'Incorrect content format -- ' . $this->message;
				break;
			case self::INCORRECT_FORMAT_DESTINATION:
				return 'Incorrect destination format -- ' . $this->message;
				break;
			case self::INCORRECT_FORMAT_ORIGIN:
				return 'Incorrect origin format -- ' . $this->message;
				break;
			case self::STATUS_NOT_READY:
				return 'Status not ready for ' . $this->message;
				break;
			case self::SENDING_FAILURE:
				return 'Sending message failed -- ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}
