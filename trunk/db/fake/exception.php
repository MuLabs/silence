<?php
namespace Beable\Kernel\Db\Fake;

use Beable\Kernel;

class Exception extends Kernel\Db\Exception
{

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		return parent::getFormatedMessage();
	}
}

