<?php
namespace Mu\Kernel;

use Mu\App;
use Mu\Kernel;

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