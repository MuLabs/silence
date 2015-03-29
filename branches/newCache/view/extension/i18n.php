<?php
namespace Mu\Kernel\View\Extension;

use Mu\App;
use Mu\Kernel;

trait i18n
{
	use Kernel\CoreTrait;

	/**
	 * @return string
	 */
	public function getCurrentLang()
	{
		$localization = $this->getApp()->getLocalizationService();
		if (!$localization || !$localization->isUrlLocaleEnabled()) {
			return '';
		}
		return $localization->getCurrentLanguage();
	}

    public function switchLang($lang, $routeName = null)
    {
		$localization = $this->getApp()->getLocalizationService();
		$parameters = $this->getApp()->getHttp()->getRequest()->getAllParameters(Kernel\Http\Request::PARAM_TYPE_GET);
		unset($parameters['rn']);
		if (!$localization || !$localization->isUrlLocaleEnabled() || !$localization->isSupportedLanguage($lang)) {
            if ($routeName) {
                return $this->getApp()->getRouteManager()->getUrl($routeName);
            } else {
                return $this->getApp()->getRouteManager()->getCurrentRouteUrl($parameters);
            }
        }

		$currentLang = $localization->getCurrentLanguage();
		$localization->setCurrentLanguage($lang);

        if ($routeName) {
            $url = $this->getApp()->getRouteManager()->getUrl($routeName);
        } else {
            $url = $this->getApp()->getRouteManager()->getCurrentRouteUrl($parameters);
        }
        $localization->setCurrentLanguage($currentLang);

		return $url;
	}
}