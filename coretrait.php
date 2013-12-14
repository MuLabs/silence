<?php
namespace Beable\Kernel;

use Beable\App;
use Beable\Kernel;

trait CoreTrait
{
	private $application;

	/**
	 * @param Kernel\Application $app
	 */
	public function setApp(Application $app)
	{
		$this->application = $app;
	}

	/**
	 * @return App\Application
	 */
	public function getApp()
	{
		return $this->application;
	}
}