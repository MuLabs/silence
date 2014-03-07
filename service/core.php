<?php
namespace Mu\Kernel\Service;

use Mu\Kernel;

abstract class Core extends Kernel\Core implements Kernel\Db\Interfaces\Requestable
{
	use Kernel\Db\Traits\Requestable;

	public function initialize()
	{

	}
}