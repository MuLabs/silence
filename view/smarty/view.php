<?php
namespace Mu\Kernel\View\Smarty;

use Mu\Kernel;

class View extends Kernel\View\View
{
	protected $smarty;
	protected $extension = 'tpl';

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

		$siteName = '';
		$site = $this->getApp()->getSiteService();
		if ($site && $site->getCurrentSiteName()) {
			$siteName = $site->getCurrentSiteName() . '/';
		}

		$language = '';
		$localization = $this->getApp()->getLocalizationService();
		if ($localization && $localization->isUrlLocaleEnabled()) {
			$language = $this->getApp()->getLocalizationService()->getCurrentLanguage() . '/';
		}

		return $this->getSmarty()->fetch('fragment/' . $target . '/' . $siteName . $language . $fragment . '.tpl');
	}
}