<?php
namespace Mu\Kernel\Bundle;

use Mu\Kernel;

abstract class Core extends Kernel\Core
{
	abstract public function initialize();

	abstract public function getMainPath();

	/**
	 * @return string
	 */
	public function getUpdatePath()
	{
		return $this->getMainPath() . '/update/';
	}
}