<?php
namespace Mu\Kernel\Db\Fake;

use Mu\Kernel;

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

