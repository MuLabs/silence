<?php
namespace Mu\Kernel\View\Twig;

use Mu\Kernel;

class View extends Kernel\View\View
{
	private $twig;

	/**
	 * @param $twig
	 */
	public function setTwig(\Twig_Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * @return \Twig_Environment
	 */
	public function getTwig()
	{
		return $this->twig;
	}

	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	public function fetch($target, $fragment = null)
	{
		if ($fragment === null) {
			return $this->getTwig()->render($target . '.twig', $this->getVars());
		}
		return $this->getTwig()->render('fragment/' . $target . '/' . $fragment . '.twig', $this->getVars());
	}
}