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
	/**
	 * @param string|array $lang
	 */
	public function registerLanguage($lang) {
		if (is_array($lang)) {
			$this->supportedLanguages = array_flip($lang);
		} else {
			$this->supportedLanguages[$lang] = true;
		}
	}

	/**
	 * @return array
	 */
	public function getSupportedLanguages() {
		return $this->supportedLanguages;
	}

	/**
	 * @param string $lang
	 * @return bool
	 */
	public function isSupportedLanguage($lang) {
		return isset($this->supportedLanguages[$lang]);
	}

	/**
	 * @param string $lang
	 * @throws Exception
	 */
	public function setCurrentLanguage($lang) {
		if (!$this->isSupportedLanguage($lang)) {
			throw new Exception($lang, Exception::LANG_NOT_SUPPORTED);
		}

		$this->currentLanguage = $lang;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getCurrentLanguage() {
		if (!isset($this->currentLanguage)) {
			if (empty($this->supportedLanguages)) {
				throw new Exception('', Exception::NO_SUPPORTED_LANGUAGES);
			}

			$this->setCurrentLanguage(reset(array_flip($this->supportedLanguages)));
		}

		return $this->currentLanguage;
	}

	/**
	 * @param string $manager
	 * @param string|array $property
	 */
	public function registerLocalizedProperty($manager, $property) {
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
	public function getLocalization(Kernel\Model\Entity $entity, $property, $lang = null) {
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
	public function setLocalization(Kernel\Model\Entity $entity, $lang, $property, $value) {
		$manager = $entity->getManager();
		$managerName = $manager->getName();

		if (!isset($this->localizedProperties[$managerName][$property])) {
			return false;
		}

		$value = '('.$entity->getId().', '.$entity->getEntityType().', "'.$lang.'", "'.$property.'", "'.$value.'")';
		$handler = $this->getApp()->getDatabase()->getHandler($this->getApp()->getDefaultDbContext());
		$sql = 'REPLACE INTO localization (idObject, objectType, lang, property, value) VALUES '.$value;
		$handler->query($sql);

		$cacheKey = $entity->getCacheKey();
		if (isset($this->localizationValuesCache[$$cacheKey])) {
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
	private function getLocalizationValue(Kernel\Model\Entity $entity, $property, $lang = null) {
		$cacheKey = $entity->getCacheKey();

		if (!$this->isSupportedLanguage($lang)) {
			$lang = $this->getCurrentLanguage();
		}
		if (!isset($this->localizationValuesCache[$cacheKey][$lang])) {
			$handler = $this->getApp()->getDatabase()->getHandler($this->getApp()->getDefaultDbContext());
			$sql = 'SELECT property, value FROM localization WHERE idObject = '.$entity->getId().' AND objectType = '.$entity->getEntityType().' AND lang = "'.$lang.'"';
			$result = $handler->query($sql);

			while(list($oneProperty, $value) = $result->fetchRow()) {
				$this->localizationValuesCache[$cacheKey][$lang][$oneProperty] = $value;
			}
		}

		return $this->localizationValuesCache[$cacheKey][$lang][$property];
	}
}