<?php
namespace Beable\Kernel\Db\PDO;

use Beable\Kernel;

class Exception extends Kernel\Db\Exception
{
	const QUERY_FAIL = 1;
	const INVALID_PROPERTY_TYPE = 2;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::QUERY_FAIL:
				return 'Mysql Error : ' . $this->message;
				break;
			case self::INVALID_PROPERTY_TYPE:
				return 'Invalid property type : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

