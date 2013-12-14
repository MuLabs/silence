<?php
namespace Beable\Kernel\Config;

use Beable\Kernel;

class Exception extends Kernel\Exception
{

	/**
	 * @return string
	 */
	public function getFormatedMessage()
	{
		switch ($this->code) {

		}
		return parent::getFormatedMessage();
	}
}

