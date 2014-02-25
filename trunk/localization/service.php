<?php
namespace Mu\Kernel\Localization;

use Mu\Bundle\Glices\Model\Manager;
use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	private $supportedLanguages = array();
	private $currentLanguage;
	private $localizedProperties = array();

	private $localizationValuesCache = array();
	private $localeFromUrl = false;
	private $urlLocaleEnabled = true;

	public function initialize()
	{
		// Extract contexts from configuration
		$languages = $this->getApp()->getConfigManager()->get('localization');

		if (!is_array($languages)) {
			return false;
		}
		foreach ($languages as $oneLang => $oneLocale) {
			$this->registerLanguage($oneLang, $oneLocale);
		}

		$httpRequest = $this->getApp()->getHttp()->getRequest();
		$uri = $httpRequest->getRequestUri();
		if ($uri[0] == '/') {
			$uri = substr($uri, 1);
		}

		$posFirstSlash = strpos($uri, '/');
		$firstParam = substr($uri, 0, $posFirstSlash);

		if ($this->isSupportedLanguage($firstParam)) {
			$this->setCurrentLanguage($firstParam);
			$uri = substr($uri, $posFirstSlash);
			$httpRequest->setRequestUri($uri);
			$this->setLocaleFromUrl(true);
		}

		return true;
	}

	/**
	 * @param string $lang
	 * @param string $locale
	 */
	private function registerLanguage($lang, $locale)
	{
		$this->supportedLanguages[$lang] = $locale;
	}

	/**
	 * @return array
	 */
	public function getSupportedLanguages()
	{
		return $this->supportedLanguages;
	}

	/**
	 * @param string $lang
	 * @return bool
	 */
	public function isSupportedLanguage($lang)
	{
		return isset($this->supportedLanguages[$lang]);
	}

	/**
	 * @param string $lang
	 * @throws Exception
	 */
	public function setCurrentLanguage($lang)
	{
		if (!$this->isSupportedLanguage($lang)) {
			throw new Exception($lang, Exception::LANG_NOT_SUPPORTED);
		}

		$this->currentLanguage = $lang;

		$locale = $this->supportedLanguages[$lang];
		$domain = 'messages';

		putenv("LC_ALL=$locale");
		setlocale(LC_ALL, $locale);
		bindtextdomain($domain, APP_LOCALE_PATH);
		bind_textdomain_codeset($domain, "UTF-8");
		textdomain($domain);
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getCurrentLanguage()
	{
		if (!isset($this->currentLanguage)) {
			if (empty($this->supportedLanguages)) {
				throw new Exception('', Exception::NO_SUPPORTED_LANGUAGES);
			}

			$defaultLanguage = $this->detectDefaultLanguage();
			$this->setCurrentLanguage($defaultLanguage);
		}

		return $this->currentLanguage;
	}

	/**
	 * @return string
	 */
	private function detectDefaultLanguage()
	{
		$acceptLang = $this->getApp()->getHttp()->getRequest()->getRequestHeader()->getAcceptLanguage();
		$langList = array();
		foreach ($acceptLang as $oneLang => $quality) {
			preg_match('#^[a-z]+#', $oneLang, $normalizedLang);


			if (count($normalizedLang) && $this->isSupportedLanguage(reset($normalizedLang))) {
				$normalizedLang = reset($normalizedLang);

				if (!isset($langList[$normalizedLang]) || $langList[$normalizedLang] < $quality) {
					$langList[$normalizedLang] = $quality;
				}
			}
		}

		if (count($langList)) {
			arsort($langList);
			return reset(array_keys($langList));
		} else {
			return reset(array_flip($this->getSupportedLanguages()));
		}

	}

	/**
	 * @param string $manager
	 * @param string|array $property
	 */
	public function registerLocalizedProperty($manager, $property)
	{
		if (is_array($property)) {
			$this->localizedProperties[$manager] = $property;
		} else {
			$this->localizedProperties[$manager][$property] = true;
		}
	}

	/**
	 * @param Kernel\Model\Entity $entity
	 * @param string $property
	 * @param string $lang
	 * @return string
	 */
	public function getLocalization(Kernel\Model\Entity $entity, $property, $lang = null)
	{
		$manager = $entity->getManager();
		$managerName = $manager->getName();

		if (!isset($this->localizedProperties[$managerName][$property])) {
			return $entity->getPropertyValue($property);
		}

		return $this->getLocalizationValue($entity, $property, $lang);
	}

	/**
	 * @param Kernel\Model\Entity $entity
	 * @param string $lang
	 * @param string $property
	 * @param mixed $value
	 * @return bool
	 */
	public function setLocalization(Kernel\Model\Entity $entity, $lang, $property, $value)
	{
		$manager = $entity->getManager();
		$managerName = $manager->getName();

		if (!isset($this->localizedProperties[$managerName][$property])) {
			return false;
		}

		$value = '(' . $entity->getId() . ', ' . $entity->getEntityType(
			) . ', "' . $lang . '", "' . $property . '", "' . $value . '")';
		$handler = $this->getApp()->getDatabase()->getHandler($this->getApp()->getDefaultDbContext());
		$sql = 'REPLACE INTO localization (idObject, objectType, lang, property, value) VALUES ' . $value;
		$handler->query($sql);

		$cacheKey = $entity->getCacheKey();
		if (isset($this->localizationValuesCache[$cacheKey])) {
			unset($this->localizationValuesCache[$cacheKey]);
		}

		return true;
	}

	/**
	 * @param Kernel\Model\Entity $entity
	 * @param string $property
	 * @param string $lang
	 * @return mixed
	 */
	private function getLocalizationValue(Kernel\Model\Entity $entity, $property, $lang = null)
	{
		$cacheKey = $entity->getCacheKey();

		if (!$this->isSupportedLanguage($lang)) {
			$lang = $this->getCurrentLanguage();
		}
		if (!isset($this->localizationValuesCache[$cacheKey][$lang])) {
			$handler = $this->getApp()->getDatabase()->getHandler($this->getApp()->getDefaultDbContext());
			$sql = 'SELECT property, value FROM localization WHERE idObject = ' . $entity->getId(
				) . ' AND objectType = ' . $entity->getEntityType() . ' AND lang = "' . $lang . '"';
			$result = $handler->query($sql);

			while (list($oneProperty, $value) = $result->fetchRow()) {
				$this->localizationValuesCache[$cacheKey][$lang][$oneProperty] = $value;
			}
		}

		return isset($this->localizationValuesCache[$cacheKey][$lang][$property]) ? $this->localizationValuesCache[$cacheKey][$lang][$property] : '';
	}

	/**
	 * @param bool $bool
	 */
	public function setLocaleFromUrl($bool)
	{
		$this->localeFromUrl = (bool)$bool;
	}

	/**
	 * @return bool
	 */
	public function isLocaleFromUrl()
	{
		return $this->localeFromUrl;
	}

	public function disableUrlLocale()
	{
		$this->urlLocaleEnabled = false;
	}

	/**
	 * @return bool
	 */
	public function isUrlLocaleEnabled()
	{
		return $this->urlLocaleEnabled;
	}
}
