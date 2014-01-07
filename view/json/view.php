<?php
namespace Mu\Kernel\View\Json;

use Mu\Kernel;

class View extends Kernel\View\View
{
	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	public function fetch($target, $fragment = null)
	{
		return json_encode($this->getVars());
	}
}