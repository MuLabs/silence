<?php
namespace Beable\Kernel\Asset;

use Beable\Kernel;

class Exception extends Kernel\Exception
{
	const ASSET_EMPTY = 1;
	const INVALID_EXTENSION = 2;

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
				list($ext, $file) = $this->message;
				return 'Invalid extension (' . $ext . ') on ' . $file;
				break;
		}
		return parent::getFormatedMessage();
	}
}

