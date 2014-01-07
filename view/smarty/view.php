<?php
namespace Mu\Kernel\View\Smarty;

use Mu\Kernel;

class View extends Kernel\View\View
{
	private $smarty;

	/**
	 * @param $smarty
	 */
	public function setSmarty(\Smarty $smarty)
	{
		$this->smarty = $smarty;
	}

	/**
	 * @return \Smarty
	 */
	public function getSmarty()
	{
		return $this->smarty;
	}

	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	public function fetch($target, $fragment = null)
	{
		$vars = $this->getVars();
		$vars['this'] = $this;
		$this->getSmarty()->assign($vars);
		if ($fragment === null) {
			return $this->getSmarty()->fetch($target . '.tpl');
		}
		return $this->getSmarty()->fetch('fragment/' . $target . '/' . $fragment . '.tpl');
	}
}