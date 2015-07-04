<?php
namespace Mu\Kernel\Site;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const MISSING_URL = 1;
	const INVALID_HOST = 2;
	const INVALID_SITE_ID = 3;


	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::MISSING_URL:
				return 'Missing site urls : ' . $this->message;
				break;
			case self::INVALID_HOST:
				return 'Invalid site host : ' . $this->message;
				break;
			case self::INVALID_SITE_ID:
				return 'Invalid site id : ' . $this->message;
				break;
			default:
				return parent::getFormatedMessage();
				break;
		}
	}
}