<?php
namespace Mu\Kernel\Db;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const CONTEXT_NOT_FOUND = 1;
	const INVALID_MANAGER = 2;
	const INVALID_PARAMETER = 3;
	const INVALID_PROPERTY_COUNT = 4;
	const INVALID_SUB_PROP_QUERY = 5;
	const INVALID_PROPERTY = 6;
	const INVALID_PROPERTY_GROUP = 7;
	const NO_DB_HANDLER = 8;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::CONTEXT_NOT_FOUND:
				return 'Context not found : ' . $this->message;
				break;
			case self::INVALID_MANAGER:
				return 'Invalid manager : ' . $this->message;
				break;
			case self::INVALID_PARAMETER:
				return 'Invalid parameter : ' . $this->message;
				break;
			case self::INVALID_PROPERTY_COUNT:
				return 'Invalid query property count : query ' . $this->message['query'] . ' --- property ' . var_dump(
					$this->message['property'],
					true
				);
				break;
			case self::INVALID_SUB_PROP_QUERY:
				return 'Invalid sub property query : ' . $this->message;
				break;
			case self::INVALID_PROPERTY:
				return 'Invalid property : ' . $this->message;
				break;
			case self::INVALID_PROPERTY_GROUP:
				return 'Invalid property group : ' . $this->message;
				break;
			case self::NO_DB_HANDLER:
				return 'No db handler found for model : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

