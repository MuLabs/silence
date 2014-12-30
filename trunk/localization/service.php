<?php
namespace Mu\Kernel\Localization;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	protected $supportedLanguages = array();
	protected $currentLanguage;
	protected $localizedProperties = array();

	protected $localizationValuesCache = array();
	protected $localeFromUrl = false;
	protected $urlLocaleEnabled = true;

	protected $properties = array(
		'localization' => array(
			'infos' => array(
				'db' => 'localization',
			),
			'keys' => array(
				'pk_id' => array(
					'type' => 'primary',
					'properties' => array(
						'idObject',
						'objectType',
						'lang',
						'property',
					),
				),
			),
			'properties' => array(
				'idObject' => array(
					'title' => 'ID Object',
					'db' => 'idObject',
					'pdo_extra' => 'UNSIGNED NOT NULL',
					'type' => 'int',
				),
				'objectType' => array(
					'title' => 'Object Type',
					'db' => 'objectType',
					'type' => 'tinyint',
					'pdo_extra' => 'UNSIGNED NOT NULL',
				),
				'lang' => array(
					'title' => 'Language',
					'db' => 'lang',
					'type' => 'char',
					'length' => 2,
					'pdo_extra' => 'NOT NULL',
				),
				'property' => array(
					'title' => 'Property name',
					'db' => 'property',
					'type' => 'varchar',
					'pdo_extra' => 'NOT NULL',
					'length' => 50,
				),
				'value' => array(
					'title' => 'Value',
					'db' => 'value',
					'type' => 'text',
				),
			)
		),
	);

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

		return true;
	}

	/**
	 * @param string $lang
	 * @param string $locale
	 */
    public function registerLanguage($lang, $locale)
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

		$langCookie = $this->getLangCookie();
		if ($langCookie) {
			$langCookie->disableRefresh();
			if ($langCookie->get('currentLang') != $lang) {
				$langCookie->set('currentLang', $lang);
				$langCookie->save();
			}
		}
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
		$langCookie = $this->getLangCookie();
        $savedLang = ($langCookie) ? $langCookie->get('currentLang') : null;

        if ($this->isSupportedLanguage($savedLang)) {
			return $savedLang;
		}

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
			$langList = array_keys($langList);
			return reset($langList);
		} else {
			$supportedLanguages = array_flip($this->getSupportedLanguages());
			return reset($supportedLanguages);
		}

	}

	/**
	 * @param string $manager
	 * @param string $property
	 * @param bool $getDefaultIfNotFound
	 */
	public function registerLocalizedProperty($manager, $property, $getDefaultIfNotFound = false)
	{
		$this->localizedProperties[$manager][$property] = $getDefaultIfNotFound;
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

		$handler = $this->getApp()->getDatabase()->getHandler($this->getApp()->getDefaultDbContext());
		$sql = 'REPLACE INTO @ (:idObject, :objectType, :lang, :property, :value) VALUES (?, ?, ?, ?, ?)';

        $aDatas = array(
            $entity->getId(),
            $entity->getEntityType(),
            $lang,
            $property,
            $value
        );

		$query = new Kernel\Db\Query($sql, $aDatas, $this);
		$handler->sendQuery($query);

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
		$manager = $entity->getManager();
		$managerName = $manager->getName();

		if (!isset($this->localizedProperties[$managerName][$property])) {
			return false;
		}

		$cacheKey = $entity->getCacheKey();

		if (!$this->isSupportedLanguage($lang)) {
			$lang = $this->getCurrentLanguage();
		}
		if (!isset($this->localizationValuesCache[$cacheKey][$lang])) {
			$handler = $this->getApp()->getDatabase()->getHandler($this->getApp()->getDefaultDbContext());
			$sql = 'SELECT :lang, :property, :value
			FROM @
			WHERE :idObject = ?
			AND :objectType = ?';

			$query = new Kernel\Db\Query($sql, array($entity->getId(), $entity->getEntityType()), $this);
			$result = $handler->sendQuery($query);

			while (list($language, $oneProperty, $value) = $result->fetchRow()) {
				$this->localizationValuesCache[$cacheKey][$language][$oneProperty] = $value;
			}
		}

		// If property allow to get default value, then get it
		if ($this->localizedProperties[$managerName][$property] === true) {
			$supportedLanguages = $this->getSupportedLanguages();
			while (!isset($this->localizationValuesCache[$cacheKey][$lang][$property]) && count(
					$supportedLanguages
				) > 0) {
				unset($supportedLanguages[$lang]);
				$lang = key($supportedLanguages);
			}
		}

		return (isset($this->localizationValuesCache[$cacheKey][$lang][$property])) ? $this->localizationValuesCache[$cacheKey][$lang][$property] : '';
	}

	/**
	 * @return Kernel\Session\Handler\Cookie
	 */
	private function getLangCookie()
	{
        $session = $this->getApp()->getSession();

        if (!$session) {
            return null;
        }
        return $session->getHandler('cookie', 'lang');
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
