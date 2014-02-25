<?php
namespace Mu\Kernel\Http\Header;

use Mu\Kernel;

class Request
{
	/**
	 * @return string
	 */
	public function getAccept()
	{
		return isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '*/*';
	}

	/**
	 * @return string
	 */
	public function getAcceptEncoding()
	{
		return isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
	}

	/**
	 * @return array
	 */
	public function getAcceptLanguage()
	{
		$acceptedLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		if (!strlen($acceptedLanguage)) {
			return array();
		}

		$acceptedLanguage = explode(',', $acceptedLanguage);
		$finalAcceptedLanguage = array();
		foreach ($acceptedLanguage as $oneLang) {
			$oneLang = explode(';', $oneLang);
			$arrayLen = count($oneLang);

			if ($arrayLen == 1) {
				$strLang = reset($oneLang);
				$quality = 1;
			} elseif ($arrayLen == 2) {
				$strLang = reset($oneLang);
				$quality = (float)str_replace('q=', '', next($oneLang));
			} else {
				continue;
			}

			$finalAcceptedLanguage[$strLang] = $quality * 100;
		}

		return $finalAcceptedLanguage;
	}

	/**
	 * @return string
	 */
	public function getConnection()
	{
		return isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '';
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	}

	/**
	 * @return string
	 */
	public function getReferer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}

	/**
	 * @return string
	 */
	public function getUserAgent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	/**
	 * @return string
	 */
	public function getHttps()
	{
		return isset($_SERVER['HTTP_HTTPS']) ? $_SERVER['HTTP_HTTPS'] : false;
	}
}
