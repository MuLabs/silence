<?php
namespace Mu\Kernel\View\Twig;

use Mu\Kernel;

class View extends Kernel\View\View
{
	private $twig;
	protected $extension = 'twig';

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
	 * @param null $fragment
	 * @return string
	 */
	public function getCacheFilepath($target, $fragment = null)
	{
		$language = $this->getApp()->getLocalizationService()->getCurrentLanguage();

		if ($fragment === null) {
			return $this->getTwig()->getCacheFilename($target . '.twig', $this->getVars());
		}
		return $this->getTwig()->getCacheFilename('fragment/' . $target . '/' . $language . '/' . $fragment . '.twig', $this->getVars());
	}

	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	public function fetch($target, $fragment = null)
	{
		$language = $this->getApp()->getLocalizationService()->getCurrentLanguage();

		if ($fragment === null) {
			return $this->getTwig()->render($target . '.twig', $this->getVars());
		}
		return $this->getTwig()->render('fragment/' . $target . '/' . $language . '/' . $fragment . '.twig', $this->getVars());
	}
}