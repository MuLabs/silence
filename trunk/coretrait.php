<?php
namespace Mu\Kernel;

use Mu\App;
use Mu\Kernel;

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