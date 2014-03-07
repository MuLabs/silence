<?php
namespace Mu\Kernel\Model;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const INVALID_ENTITY_TYPE = 3;
	const INVALID_ENTITY_CLASSNAME = 4;
	const INVALID_ENTITY = 5;
	const INVALID_CREATE_PARAMETERS = 6;
	const INVALID_ENTITY_BUNDLE = 7;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::INVALID_ENTITY_TYPE:
				return 'Invalid entity type : ' . $this->message;
				break;
			case self::INVALID_ENTITY_CLASSNAME:
				return 'Invalid entity classname : ' . $this->message;
				break;
			case self::INVALID_ENTITY_BUNDLE:
				return 'Invalid entity bundle name : ' . $this->message;
				break;
			case self::INVALID_ENTITY:
				return 'Invalid Entity : ' . $this->message;
				break;
			case self::INVALID_CREATE_PARAMETERS:
				return 'Invalid creation parameters : ' . $this->message;
				break;
			default:
				return parent::getFormatedMessage();
				break;
		}
	}
}