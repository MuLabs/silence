<?php
namespace Mu\Kernel\Http;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	protected $httpRequest;
	protected $httpResponse;
    private $bCheck = false;

	public function __construct()
	{
		$this->initRequest();
		$this->initResponse();
	}

	public function initRequest()
	{
		$request = new Request();
		$request->setHeader(new Header\Request());
		if (!defined('MFC_TEST')) {
			if (isset($_SERVER['REQUEST_METHOD'])) {
				$request->setMethod($_SERVER['REQUEST_METHOD']);
			}
		} else {
			$request->setMethod('GET');
		}
		if (isset($_SERVER['REQUEST_URI'])) {
			$request->setRequestUri($_SERVER['REQUEST_URI']);
		}
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $request->setHttpAccept($_SERVER['HTTP_ACCEPT']);
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $request->setContentType($_SERVER['CONTENT_TYPE']);
        }

		$this->httpRequest = $request;
	}

	public function initResponse()
	{
		$response = new Response();
		$response->setHeader(new Header\Response());
		$this->httpResponse = $response;
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
        // Call localization service to modify request URI if needed:
        if (!$this->bCheck) {
            $this->bCheck = true;

            $localization = $this->getApp()->getLocalizationService();
            if ($localization) {
                $uri = $this->httpRequest->getRequestUri();
                if ($uri[0] == '/') {
                    $uri = substr($uri, 1);
                }

                $posFirstSlash = strpos($uri, '/');
                $firstParam = substr($uri, 0, $posFirstSlash);

                if ($localization->isSupportedLanguage($firstParam)) {
                    $localization->setCurrentLanguage($firstParam);
                    $uri = substr($uri, $posFirstSlash);
                    $this->httpRequest->setRequestUri($uri);
                    $localization->setLocaleFromUrl(true);
                }
            }
        }

		return $this->httpRequest;
	}

	/**
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->httpResponse;
	}
}