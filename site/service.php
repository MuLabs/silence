<?php
namespace Mu\Kernel\Site;

use Mu\Bundle\Glices\Model\Manager;
use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	const BO_ID = 0;

	private $sites = array();
	private $sitesUrl = array();
	private $currentSite;

	/**
	 * @param int $siteId
	 * @param string $siteKey
	 * @throws Exception
	 */
	public function register($siteId, $siteKey)
	{
		$siteId = (int)$siteId;
		if ($siteId == self::BO_ID) {
			throw new Exception($siteId, Exception::INVALID_SITE_ID);
		}
		$this->sites[$siteKey] = (int)$siteId;
	}

	/**
	 * @param string $boKey
	 */
	public function registerBo($boKey)
	{
		$this->sites[$boKey] = self::BO_ID;
	}

	/**
	 * @return array
	 */
	public function getSites()
	{
		return $this->sites;
	}

	/**
	 * @return string
	 */
	public function getCurrentSiteUrl()
	{
		return $this->sitesUrl[$this->currentSite];
	}

	/**
	 * @return string
	 */
	public function getCurrentSiteName()
	{
		$sites = array_flip($this->sites);
		return $sites[$this->currentSite];
	}

	/**
	 * @return mixed
	 */
	public function getCurrentSite()
	{
		return $this->currentSite;
	}

	/**
	 * @throws Exception
	 */
	public function loadSiteUrl()
	{
		$urlList = $this->getApp()->getConfigManager()->get('url');

		foreach ($urlList as $key => $oneUrl) {
			if (!is_string($oneUrl)) {
				continue;
			}

			if (!isset($this->sites[$key])) {
				continue;
			}

			$this->sitesUrl[$this->sites[$key]] = $oneUrl;
		}

		if (count($this->sitesUrl) != count($this->sites)) {
			$missing = array();
			foreach ($this->sites as $key => $id) {
				if (!isset($this->sitesUrl[$id])) {
					$missing[] = $key;
				}
			}

			throw new Exception(implode(', ', $missing), Exception::MISSING_URL);
		}

		$this->setCurrentSite();
	}

	/**
	 * @throws Exception
	 */
	public function setCurrentSite()
	{
		$httpRequestHeader = $this->getApp()->getHttp()->getRequest()->getRequestHeader();
		$host = $httpRequestHeader->getHost();

		$urls = array_flip($this->sitesUrl);

		foreach ($urls as $oneUrl => $oneId) {
			if (strpos($oneUrl, $host) !== false) {
				$this->currentSite = $oneId;
				break;
			}
		}

		if (!is_int($this->currentSite)) {
			throw new Exception($host, Exception::INVALID_HOST);
		}
	}

	/**
	 * @return bool
	 */
	public function isCurrentSiteBo()
	{
		return $this->currentSite == self::BO_ID;
	}
}
