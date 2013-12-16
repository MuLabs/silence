<?php
namespace Beable\Kernel\Http;

use Beable\Kernel;

class Service extends Kernel\Service\Core
{
	private $httpRequest;
	private $httpResponse;

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