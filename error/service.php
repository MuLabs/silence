<?php
namespace Mu\Kernel\Error;

use Mu\Kernel;

class Service extends Kernel\Service\Core
{
	/**
	 * Permanent redirection
	 */
	public function error301()
	{
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(301);
		$response->send();
	}

	/**
	 * Redirection error
	 */
	public function error302()
	{
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(302);
		$response->send();
	}

	/**
	 * Unauthorized
	 */
	public function error401()
	{
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(401);
		$response->send();
	}

	/**
	 * Not found
	 * @param string $message
	 */
	public function error404($message = 'Not found')
	{
		if (!$this->getApp()->isProduction()) {
			exit('ERROR 404 : ' . $message);
		}
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(404);
		$response->send();
	}

	/**
	 * Server exception
	 */
	public function error500()
	{
		$response = $this->getApp()->getHttp()->getResponse();
		$response->getHeader()->setCode(500);
		$response->send();
	}
}
