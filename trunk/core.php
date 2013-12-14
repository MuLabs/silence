<?php
namespace Beable\Kernel;

use Beable\App;
use Beable\Kernel;

class Core
{
	use Kernel\CoreTrait;
	private $classname;

	/**
	 * Get class name without namespace
	 * @return string
	 */
	public function getClassName()
	{
		if (!isset($this->classname)) {
			$this->classname = join('', array_slice(explode('\\', strtolower(get_class($this))), -1));
		}
		return $this->classname;
	}
}