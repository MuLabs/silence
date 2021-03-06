<?php
namespace Mu\Kernel\View\Twig;

use Mu\Kernel;

class View extends Kernel\View\View
{
	protected $twig;
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

		$language = '';
		$localization = $this->getApp()->getLocalizationService();
		if ($localization && $localization->isUrlLocaleEnabled()) {
			$language = $this->getApp()->getLocalizationService()->getCurrentLanguage() . '/';
		}

		$siteName = '';
		$site = $this->getApp()->getSiteService();
		if ($site && $site->getCurrentSiteName()) {
			$siteName = $site->getCurrentSiteName() . '/';
		}

		if ($fragment === null) {
			return $this->getTwig()->getCacheFilename($target . '.twig', $this->getVars());
		}
		return $this->getTwig()->getCacheFilename(
			'/fragment/' . $target . '/' . $siteName . $language . $fragment . '.twig',
			$this->getVars()
		);
	}

	/**
	 * @inheritdoc
	 */
	public function render()
	{
		if ($this->fragment === null) {
			return $this->getTwig()->render($this->template . '.twig', $this->getVars());
		}
		return $this->getTwig()->render(
			'/fragment/' . $this->template . '/' . $this->fragment . '.twig',
			$this->getVars()
		);
	}
}