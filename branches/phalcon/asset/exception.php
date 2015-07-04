<?php
namespace Mu\Kernel\Asset;

use Mu\Kernel;

class Exception extends Kernel\Exception
{
	const ASSET_EMPTY = 1;
	const INVALID_EXTENSION = 2;
	const FILE_NOT_FOUND = 3;

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {
			case self::ASSET_EMPTY:
				return 'Asset is empty : ' . $this->message;
				break;
			case self::INVALID_EXTENSION:
				return 'Invalid extension : ' . $this->message;
				break;
			case self::FILE_NOT_FOUND:
				return 'File not found : ' . $this->message;
				break;
		}
		return parent::getFormatedMessage();
	}
}

