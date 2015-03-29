<?php
namespace Mu\Kernel\View\Extension;

use Mu\App;
use Mu\Kernel;

trait Core
{
	use Kernel\CoreTrait;

	/**
	 * @param Kernel\Model\Entity $entity
	 * @param string $property
	 * @param string $lang
	 * @return mixed
	 */
	public function getLoc(Kernel\Model\Entity $entity, $property, $lang = null) {
		return $this->getApp()->getLocalizationService()->getLocalization($entity, $property, $lang);
	}

	/**
	 * @param string $routeName
	 * @param array $parameters
	 * @return string
	 */
	public function getUrl($routeName, array $parameters = array())
	{
		return $this->getApp()->getRouteManager()->getUrl($routeName, $parameters);
	}

	/**
	 * @param string $routeName
	 * @param array $parameters
	 * @return string
	 */
	public function getUrlBase64($routeName, array $parameters = array())
	{
		return base64_encode($this->getUrl($routeName, $parameters));
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function thisUrl(array $parameters = array())
	{
		return $this->getApp()->getRouteManager()->getCurrentRouteUrl($parameters);
	}

	/**
	 * Get root url of a given site
	 * @param $siteName
	 * @return string
	 */
	public function getUrlSite($siteName)
	{
		return $this->getApp()->getUrlSite($siteName);
	}

	/**
	 * @param string $file
	 * @return string
	 */
	public function getUrlStatic($file)
	{
		return $this->getApp()->getUrlStatic($file);
	}

	/**
	 * @param string $controllerName
	 * @param string $fragmentName
	 * @param array $parameters
	 * @return mixed
	 */
	public function getFragmentUrl($controllerName, $fragmentName, array $parameters = array())
	{
		return $this->getApp()->getRouteManager()->getFragmentUrl($controllerName, $fragmentName, $parameters);
	}

	/**
	 * @param string $controllerName
	 * @param $fragmentName
	 * @param array $parameters
	 * @return string
	 */
	public function addFragment($controllerName, $fragmentName, array $parameters = array())
	{
		if ($this->getApp()->isSsiEnabled()) {
			return '<!--# block name="' . $fragmentName . '" -->&nbsp;<!-- endblock -->
			<!--# include file="' . $this->getFragmentUrl(
				$controllerName,
				$fragmentName,
				$parameters
			) . '" stub="' . $fragmentName . '" -->';
		} elseif ($this->getApp()->isEsiEnabled()) {
			return '<esi:include src="' . $this->getFragmentUrl(
				$controllerName,
				$fragmentName,
				$parameters
			) . '" onerror="continue"></esi:include>';
		} else {
			return $this->getApp()->fragmentRedirect($controllerName, $fragmentName, $parameters);
		}
	}

	/**
	 * @param string $fragmentName
	 * @param array $parameters
	 * @return string
	 */
	public function thisFragment($fragmentName, array $parameters = array())
	{
		return $this->addFragment(
			$this->getApp()->getRouteManager()->getCurrentRoute()->getName(),
			$fragmentName,
			$parameters
		);
	}

	/**
	 * @param string $fileName
	 * @param ...
	 * @return string
	 */
	public function asset($fileName)
	{
		$files = func_get_args();
		return $this->getApp()->getAssetManager()->getAsset($files);
	}

    /**
     * @param $date
     * @param $format
     * @return string
     */
    public function getConvertedDate($date = null, $format = null)
	{
		$format = ($format) ? $format : $this->getApp()->getLocalizationService()->getCurrentLanguage();
		return $this->getApp()->getToolbox()->getConvertedDate($date, $format);
	}
}