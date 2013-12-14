<?php
namespace Beable\Kernel\View\Extension;

use Beable\App;
use Beable\Kernel;

trait Core
{
	use Kernel\CoreTrait;

	/**
	 * @param string $controllerName
	 * @param array $parameters
	 * @return string
	 */
	public function getUrl($controllerName, array $parameters = array())
	{
		return $this->getApp()->getRouteManager()->getUrl($controllerName, $parameters);
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
		if ($this->getApp()->isEsiEnabled()) {
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
}