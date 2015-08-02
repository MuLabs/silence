<?php
namespace Mu\Kernel;

use Mu\App;
use Mu\Kernel;

interface CoreInterface
{
	/**
	 * @param Kernel\Application $app
	 */
	public function setApp(Application $app);

	/**
	 * @return App\Application
	 */
	public function getApp();

	/**
	 * @return Kernel\Log\Service
	 */
	public function getLogger();

	/**
	 * @param Kernel\Log\Service $logger
	 */
	public function setLogger(Kernel\Log\Service $logger);

	/**
	 * @param string $section
	 * @param mixed $log
	 * @throws Exception
	 */
	public function log($section, $log);

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getConstant($name);

	/**
	 * @param string $trait
	 * @return bool
	 */
	public function hasTrait($trait);
}